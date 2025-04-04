<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Driver;
use App\Models\DriverTask;
use App\Models\ReturnOrderItem;
use App\Enums\DriverStatus;
use App\Enums\ReturnOrderStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DriverServices
{
    public function __construct(
        private readonly OrderServices $orderServices
    ) {
    }

    public function assignOrdersToDriver(Collection $orders, int $driverId): void
    {
        $tasks = $orders->map(fn($order) => [
            'driver_id' => $driverId,
            'order_id' => $order->id,
            'driver_assisment_officer_id' => auth()->id(),
            'status' => DriverStatus::PENDING,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        DriverTask::upsert(
            $tasks,
            ['order_id'],
            ['driver_id', 'driver_assisment_officer_id', 'status', 'updated_at']
        );
    }

    public function assignReturnOrdersToDriver(Collection $returnOrders, int $driverId): void
    {
        $returnOrders->each(function ($item) use ($driverId) {
            $item->update([
                'driver_id' => $driverId,
                'status' => ReturnOrderStatus::DRIVER_PICKUP,
            ]);
        });
    }

    private function processItemsDeliveryToCustomer(Order $order, Collection $records, array $receivedItems): array
    {
        $itemsToReturn = collect($receivedItems)->map(function ($receivedItem) use ($records) {
            $orderItem = $records->firstWhere('id', $receivedItem['item_id']);

            // Calculate differences (quantities to return)
            $packetsToReturn = max(0, $orderItem->packets_quantity - $receivedItem['packets_quantity']);
            $piecesToReturn = max(0, $orderItem->piece_quantity - $receivedItem['piece_quantity']);

            // Only return if there are differences
            if ($packetsToReturn > 0 || $piecesToReturn > 0) {
                return [
                    'order_item' => $orderItem,
                    'product_id' => $orderItem->product_id,
                    'packets_quantity' => $packetsToReturn,
                    'packet_price' => $orderItem->packet_price,
                    'packet_cost' => $orderItem->packet_cost,
                    'piece_quantity' => $piecesToReturn,
                    'piece_price' => $orderItem->piece_price,
                    'return_reason' => 'لم يتم استلام الكمية كاملة',
                    'notes' => 'إرجاع تلقائي للكمية غير المستلمة',
                    'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
                    'driver_id' => auth()->id()
                ];
            }
            return null;
        })->filter()->values()->toArray();

        if (!empty($itemsToReturn)) {
            $returnItems = $this->orderServices->returnItems($order, $itemsToReturn);
            $this->markReturnItemsAsReceivedFromCustomer($returnItems);
        }

        return $itemsToReturn;
    }

    public function markReturnItemsAsReceivedFromCustomer(Collection $returnItems): void
    {
        $driver = Driver::find(auth()->id());
        DB::transaction(function () use ($returnItems, $driver) {
            $returnItems->each(function (ReturnOrderItem $item) use ($driver) {
                // Update return item status
                $item->update([
                    'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER
                ]);

                // Check if product already exists in driver's returned products
                $existingPivot = $driver->returnedProducts()->where('product_id', $item->product_id)->first();

                if ($existingPivot) {
                    // Update existing record by adding quantities
                    $driver->returnedProducts()->updateExistingPivot($item->product_id, [
                        'packets_quantity' => $existingPivot->pivot->packets_quantity + $item->packets_quantity,
                        'piece_quantity' => $existingPivot->pivot->piece_quantity + $item->piece_quantity
                    ]);
                } else {
                    // Add new record
                    $driver->returnedProducts()->attach($item->product_id, [
                        'packets_quantity' => $item->packets_quantity,
                        'piece_quantity' => $item->piece_quantity
                    ]);
                }
            });
        });
    }

    public function deliverOrder(Order $order, Collection $records, array $receivedItems): void
    {
        DB::transaction(function () use ($order, $records, $receivedItems) {
            // Process delivery and get any return items
            $this->processItemsDeliveryToCustomer($order, $records, $receivedItems);

            // Update order status
            $order->update(['status' => OrderStatus::DELIVERED]);

            // Update driver task status
            $order->driverTask()->update(['status' => DriverStatus::DONE]);

            // Update driver balance with order's net total
            $driver = Driver::find(auth()->id());
            $driver->account()->increment('balance', $order->netTotal);
        });
    }

    public function returnAllOrderItems(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Get all the items from the order
            $itemsToReturn = $order->items->map(function ($orderItem) {
                return [
                    'order_item' => $orderItem,
                    'product_id' => $orderItem->product_id,
                    'packets_quantity' => $orderItem->packets_quantity,
                    'packet_price' => $orderItem->packet_price,
                    'packet_cost' => $orderItem->packet_cost,
                    'piece_quantity' => $orderItem->piece_quantity,
                    'piece_price' => $orderItem->piece_price,
                    'return_reason' => 'إرجاع كامل الطلب',
                    'notes' => 'تم إرجاع جميع الأصناف من قبل مندوب التسليم',
                    'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
                    'driver_id' => auth()->id()
                ];
            })->toArray();

            // Process the return items
            $returnItems = $this->orderServices->returnItems($order, $itemsToReturn);
            $this->markReturnItemsAsReceivedFromCustomer($returnItems);

            // make discount value = 0 so that netTotal don't be negative
            // Update order status
            $order->update(['discount' => 0, 'status' => OrderStatus::DELIVERED]);

            // Update driver task status
            $order->driverTask()->update(['status' => DriverStatus::DONE]);
        });
    }
}
