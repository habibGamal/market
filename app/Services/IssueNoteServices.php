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

    /**
     * Create issue note items from return purchase invoice
     *
     * @param IssueNote $issueNote
     * @param \App\Models\ReturnPurchaseInvoice $returnPurchaseInvoice
     */
    public function fromReturnPurchaseInvoice(IssueNote $issueNote, \App\Models\ReturnPurchaseInvoice $returnPurchaseInvoice): void
    {
        DB::transaction(function () use ($issueNote, $returnPurchaseInvoice) {
            // Load the return purchase invoice items
            $returnPurchaseInvoice->load('items.product');

            $issueNoteItems = $returnPurchaseInvoice->items->map(function ($item) use ($issueNote) {
                return [
                    'issue_note_id' => $issueNote->id,
                    'product_id' => $item->product_id,
                    'packets_quantity' => $item->packets_quantity,
                    'piece_quantity' => $item->piece_quantity,
                    'packet_cost' => $item->packet_cost,
                    'release_date' => $item->release_date,
                    'total' => $item->total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert issue note items
            IssueNoteItem::upsert(
                $issueNoteItems,
                ['issue_note_id', 'product_id', 'release_date'],
                ['packets_quantity', 'piece_quantity', 'packet_cost', 'total']
            );

            // Update return purchase invoice with issue note id
            $returnPurchaseInvoice->update([
                'issue_note_id' => $issueNote->id
            ]);
        });
    }

    /**
     * Close issue note and remove products from stock for return purchase invoice
     *
     * @param IssueNote $issueNote
     */
    public function closeReturnPurchaseIssueNote(IssueNote $issueNote): void
    {
        DB::transaction(function () use ($issueNote) {
            // Load necessary relationships
            $issueNote->load(['items.product']);

            // Remove quantities from stock for each item
            $issueNote->items->each(function ($item) {
                $this->stockServices->removeFromUnavailable($item->product, [
                    $item->release_date => ($item->packets_quantity * $item->product->packet_to_piece) + $item->piece_quantity
                ]);
            });
        });
    }

    /**
     * Create issue note items from waste
     *
     * @param IssueNote $issueNote
     * @param \App\Models\Waste $waste
     */
    public function fromWaste(IssueNote $issueNote, \App\Models\Waste $waste): void
    {
        DB::transaction(function () use ($issueNote, $waste) {
            // Load the waste items
            $waste->load('items.product');

            $issueNoteItems = $waste->items->map(function ($item) use ($issueNote) {
                return [
                    'issue_note_id' => $issueNote->id,
                    'product_id' => $item->product_id,
                    'packets_quantity' => $item->packets_quantity,
                    'piece_quantity' => $item->piece_quantity,
                    'packet_cost' => $item->packet_cost,
                    'release_date' => $item->release_date,
                    'total' => $item->total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert issue note items
            IssueNoteItem::upsert(
                $issueNoteItems,
                ['issue_note_id', 'product_id', 'release_date'],
                ['packets_quantity', 'piece_quantity', 'packet_cost', 'total']
            );

            // Update waste with issue note id
            $waste->update([
                'issue_note_id' => $issueNote->id
            ]);
        });
    }

    /**
     * Close issue note and remove products from stock for waste
     *
     * @param IssueNote $issueNote
     */
    public function closeWasteIssueNote(IssueNote $issueNote): void
    {
        DB::transaction(function () use ($issueNote) {
            // Load necessary relationships
            $issueNote->load(['items.product']);

            // Remove quantities from stock for each item
            $issueNote->items->each(function ($item) {
                $this->stockServices->removeFromUnavailable($item->product, [
                    $item->release_date => ($item->packets_quantity * $item->product->packet_to_piece) + $item->piece_quantity
                ]);
            });
        });
    }
}
