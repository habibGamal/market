<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    public function saving(OrderItem $orderItem): void
    {
        $orderItem->total = ($orderItem->packets_quantity * $orderItem->packet_price) +
            ($orderItem->piece_quantity * $orderItem->piece_price);

        $totalCost = ($orderItem->packets_quantity * $orderItem->packet_cost) +
            (($orderItem->piece_quantity / $orderItem->product->packet_to_piece) * $orderItem->packet_cost);

        $orderItem->profit = $orderItem->total - $totalCost;
    }
}
