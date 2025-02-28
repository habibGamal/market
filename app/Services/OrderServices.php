<?php

namespace App\Services;

use App\Enums\ReturnOrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CancelledOrderItem;
use App\Models\ReturnOrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderServices
{
    public function __construct(
        private readonly StockServices $stockServices
    ) {}

    /**
     * Add multiple order items to an order
     *
     * @param Order $order The order to add items to
     * @param array $items Array of item data to create
     * @return Collection<OrderItem>
     */
    public function addOrderItems(Order $order, array $items): Collection
    {
        return DB::transaction(function () use ($order, $items) {
            // Reserve stock for each item
            foreach ($items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $totalPieces = (($item['packets_quantity'] ?? 0) * $product->packet_to_piece) + ($item['piece_quantity'] ?? 0);
                $this->stockServices->reserve($product, $totalPieces);
            }

            $createdItems = $order->items()->createMany($items);
            $this->updateOrderTotal($order);
            return collect($createdItems);
        });
    }

    /**
     * Add a single order item to an order
     *
     * @param Order $order The order to add the item to
     * @param array $data The item data to create
     * @return OrderItem
     */
    public function addOrderItem(Order $order, array $data): OrderItem
    {
        return DB::transaction(function () use ($order, $data) {
            $item = $order->items()->create($data);
            $this->updateOrderTotal($order);
            return $item;
        });
    }

    /**
     * Add multiple cancelled items to an order
     *
     * @param Order $order The order to add cancelled items to
     * @param array $items
     * ex: [
     *    [ 'order_item' => OrderItem, 'packets_quantity' => 1 , 'piece_quantity' => 1 , notes=>'test' ],
     *    [ 'order_item' => OrderItem, 'packets_quantity' => 5 , 'piece_quantity' => 9 , notes=>'test' ],
     * ]
     * @return Collection<CancelledOrderItem>
     */
    public function cancelledItems(Order $order, array $items)
    {
        return DB::transaction(function () use ($order, $items) {
            foreach ($items as $itemData) {
                $orderItem = $itemData['order_item'];
                $orderItem->load('product');  // Ensure product is loaded

                // Undo stock reservation for cancelled quantities
                $totalPiecesToUndo = ($itemData['packets_quantity'] * $orderItem->product->packet_to_piece) + $itemData['piece_quantity'];
                $this->stockServices->undoReserve($orderItem->product, $totalPiecesToUndo);

                // Create the cancelled item record
                $order->cancelledItems()->create([
                    'product_id' => $itemData['product_id'],
                    'packets_quantity' => $itemData['packets_quantity'],
                    'packet_price' => $itemData['packet_price'],
                    'packet_cost' => $orderItem->packet_cost,
                    'piece_quantity' => $itemData['piece_quantity'],
                    'piece_price' => $itemData['piece_price'],
                    'officer_id' => auth()->id(),
                    'notes' => $itemData['notes'] ?? null
                ]);

                // Update the original order item quantities
                $newPacketsQuantity = $orderItem->packets_quantity - $itemData['packets_quantity'];
                $newPieceQuantity = $orderItem->piece_quantity - $itemData['piece_quantity'];

                // Remove order item if both quantities are zero
                if ($newPacketsQuantity == 0 && $newPieceQuantity == 0) {
                    $orderItem->delete();
                } else {
                    $orderItem->update([
                        'packets_quantity' => $newPacketsQuantity,
                        'piece_quantity' => $newPieceQuantity
                    ]);
                }
            }

            $this->updateOrderTotal($order);
        });
    }

    /**
     * Add multiple return items to an order
     *
     * @param Order $order The order to add return items to
     * @param array $items
     * @return Collection<ReturnOrderItem>
     */
    public function returnItems(Order $order, array $items)
    {
        return DB::transaction(function () use ($order, $items) {
            $returnItems = [];
            foreach ($items as $itemData) {
                $orderItem = $itemData['order_item'];

                // Get total previously returned quantities for this item
                $previousReturns = $order->returnItems()
                    ->where('product_id', $itemData['product_id'])
                    ->selectRaw('SUM(packets_quantity) as total_packets, SUM(piece_quantity) as total_pieces')
                    ->first();

                $availablePackets = $orderItem->packets_quantity - ($previousReturns->total_packets ?? 0);
                $availablePieces = $orderItem->piece_quantity - ($previousReturns->total_pieces ?? 0);

                if ($itemData['packets_quantity'] > $availablePackets || $itemData['piece_quantity'] > $availablePieces) {
                    throw new \Exception('الكمية المطلوبة للإرجاع غير متوفرة للمنتج ' . $orderItem->product->name . '. الكمية المتاحة: ' . $availablePackets . ' باكيت و ' . $availablePieces . ' قطعة');
                }

                $returnItems[] = [
                    'product_id' => $itemData['product_id'],
                    'packets_quantity' => $itemData['packets_quantity'],
                    'packet_price' => $itemData['packet_price'],
                    'packet_cost' => $orderItem->packet_cost,
                    'piece_quantity' => $itemData['piece_quantity'],
                    'piece_price' => $itemData['piece_price'],
                    'return_reason' => $itemData['return_reason'],
                    'notes' => $itemData['notes'] ?? null,
                    'status' => $itemData['status'] ?? ReturnOrderStatus::PENDING,
                    'driver_id' => $itemData['driver_id'] ?? null
                ];
            }
            return $order->returnItems()->createMany($returnItems);
        });
    }

    /**
     * Remove return items from an order
     *
     * @param Order $order The order to remove return items from
     * @param Collection $returnItems Array of return item IDs to remove
     * @return void
     */
    public function removeReturnItems(Order $order, Collection $returnItems): void
    {
        DB::transaction(function () use ($order, $returnItems) {
            $order
                ->returnItems()
                ->whereIn('id', $returnItems->pluck('id'))
                ->delete();
        });
    }

    /**
     * Update the order's total based on its items
     * Calculates: regular items total - (cancelled items total + returned items total)
     *
     * @param Order $order The order to update the total for
     */
    public function updateOrderTotal(Order $order): void
    {
        $itemsTotal = $order->items()->sum('total');

        $order->update([
            'total' => $itemsTotal
        ]);
    }
}
