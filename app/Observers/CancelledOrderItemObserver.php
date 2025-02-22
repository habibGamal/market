<?php

namespace App\Observers;

use App\Models\CancelledOrderItem;

class CancelledOrderItemObserver
{
    public function creating(CancelledOrderItem $cancelledOrderItem): void
    {
        $cancelledOrderItem->total = ($cancelledOrderItem->packets_quantity * $cancelledOrderItem->packet_price) +
            ($cancelledOrderItem->piece_quantity * $cancelledOrderItem->piece_price);
    }
}
