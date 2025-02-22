<?php

namespace App\Observers;

use App\Models\ReturnOrderItem;

class ReturnOrderItemObserver
{
    public function creating(ReturnOrderItem $returnOrderItem): void
    {
        $returnOrderItem->total = ($returnOrderItem->packets_quantity * $returnOrderItem->packet_price) +
            ($returnOrderItem->piece_quantity * $returnOrderItem->piece_price);
    }
}
