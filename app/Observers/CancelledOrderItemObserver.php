<?php

namespace App\Observers;

use App\Models\CancelledOrderItem;

class CancelledOrderItemObserver
{
    public function creating(CancelledOrderItem $cancelledOrderItem): void
    {
        $cancelledOrderItem->total = ($cancelledOrderItem->packets_quantity * $cancelledOrderItem->packet_price) +
            ($cancelledOrderItem->piece_quantity * $cancelledOrderItem->piece_price);

        $totalCost = ($cancelledOrderItem->packets_quantity * $cancelledOrderItem->packet_cost) +
            (($cancelledOrderItem->piece_quantity / $cancelledOrderItem->product->packet_to_piece) * $cancelledOrderItem->packet_cost);

        $cancelledOrderItem->profit = $cancelledOrderItem->total - $totalCost;
    }
}
