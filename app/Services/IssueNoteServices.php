<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\DriverStatus;
use App\Models\Order;
use App\Models\DriverTask;
use App\Models\ReceiptNote;
use DB;
use Illuminate\Support\Collection;
use App\Models\IssueNote;
use App\Models\IssueNoteItem;

class IssueNoteServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {
    }

    /**
     * Issue note to stock
     *
     * @param IssueNote $issueNote
     * @param Collection<\App\Models\Order> $orders
     */
    public function fromOrders(IssueNote $issueNote, Collection $orders)
    {
        DB::transaction(function () use ($issueNote, $orders) {
            // Load all order items
            $orders->each->loadMissing('items.product');

            // Group items by product and sum quantities
            $groupedItems = $orders->pluck('items')
                ->flatten()
                ->groupBy('product_id')
                ->map(function ($items) {
                    return [
                        'packets_quantity' => $items->sum('packets_quantity'),
                        'piece_quantity' => $items->sum('piece_quantity'),
                        'product' => $items->first()->product,
                        'packet_cost' => $items->first()->product->packet_cost,
                    ];
                });

            $issueNoteItems = [];

            foreach ($groupedItems as $productId => $data) {
                $product = $data['product'];
                $totalPieceQuantity = ($data['packets_quantity'] * $product->packet_to_piece) + $data['piece_quantity'];

                // Get reserved quantities by release dates
                $reservedQuantities = $this->stockServices->getReservedQuantities($product, $totalPieceQuantity);

                // Prepare issue note items for each release date
                foreach ($reservedQuantities as $releaseDate => $quantity) {
                    // Calculate packets and pieces based on the reserved quantity
                    $packetSize = $product->packet_to_piece;
                    $packetsQuantity = (int) ($quantity / $packetSize);
                    $pieceQuantity = $quantity % $packetSize;

                    $issueNoteItems[] = [
                        'issue_note_id' => $issueNote->id,
                        'product_id' => $product->id,
                        'packets_quantity' => $packetsQuantity,
                        'piece_quantity' => $pieceQuantity,
                        'packet_cost' => $data['packet_cost'],
                        'release_date' => $releaseDate,
                        'total' => ($packetsQuantity * $data['packet_cost']) + ($pieceQuantity * ($data['packet_cost'] / $packetSize)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Upsert issue note items
            IssueNoteItem::upsert(
                $issueNoteItems,
                ['issue_note_id', 'product_id', 'release_date'],
                ['packets_quantity', 'piece_quantity', 'packet_cost', 'total']
            );

            // Update all orders status to preparing and assign issue note ID
            Order::whereIn('id', $orders->pluck('id'))->update([
                'status' => OrderStatus::PREPARING,
                'issue_note_id' => $issueNote->id
            ]);
        });
    }

    /**
     * Close issue note and mark orders as out for delivery
     *
     * @param IssueNote $issueNote
     */
    public function closeOrdersIssueNote(IssueNote $issueNote): void
    {
        DB::transaction(function () use ($issueNote) {
            // Load necessary relationships
            $issueNote->load(['items', 'orders']);

            // Remove quantities from stock for each item
            $issueNote->items->each(function ($item) {
                $this->stockServices->removeFromReserve($item->product, [
                    $item->release_date => ($item->packets_quantity * $item->product->packet_to_piece) + $item->piece_quantity
                ]);
            });

            // Update orders status to out for delivery
            $issueNote->orders()->update([
                'status' => OrderStatus::OUT_FOR_DELIVERY
            ]);

            // Update related driver tasks status to received
            DriverTask::whereIn('order_id', $issueNote->orders->pluck('id'))
                ->update(['status' => DriverStatus::RECEIVED]);
        });
    }
}
