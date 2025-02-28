<?php

namespace App\Observers;

use App\Models\ReturnOrderItem;

class ReturnOrderItemObserver
{
    public function creating(ReturnOrderItem $returnOrderItem): void
    {
        $returnOrderItem->total = ($returnOrderItem->packets_quantity * $returnOrderItem->packet_price) +
            ($returnOrderItem->piece_quantity * $returnOrderItem->piece_price);

        $totalCost = ($returnOrderItem->packets_quantity * $returnOrderItem->packet_cost) +
            (($returnOrderItem->piece_quantity / $returnOrderItem->product->packet_to_piece) * $returnOrderItem->packet_cost);

        $returnOrderItem->profit = $returnOrderItem->total - $totalCost;
    }
}
