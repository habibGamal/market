<?php

namespace App\Observers;

use App\Models\StockCountingItem;

class StockCountingItemObserver
{
    /**
     * Handle the StockCountingItem "created" event.
     */
    public function created(StockCountingItem $stockCountingItem): void
    {
        //
    }

    /**
     * Handle the StockCountingItem "updated" event.
     */
    public function updated(StockCountingItem $stockCountingItem): void
    {
        //
    }

    /**
     * Handle the StockCountingItem "saving" event.
     */
    public function saving(StockCountingItem $stockCountingItem): void
    {
        // Calculate the total difference based on the change in stock quantities
        if ($stockCountingItem->product && $stockCountingItem->product->packet_to_piece > 0) {
            $oldTotalInPieces = ($stockCountingItem->old_packets_quantity * $stockCountingItem->product->packet_to_piece) + $stockCountingItem->old_piece_quantity;
            $newTotalInPieces = ($stockCountingItem->new_packets_quantity * $stockCountingItem->product->packet_to_piece) + $stockCountingItem->new_piece_quantity;
            $diffInPieces = $newTotalInPieces - $oldTotalInPieces;

            $stockCountingItem->total_diff = ($diffInPieces / $stockCountingItem->product->packet_to_piece) * $stockCountingItem->packet_cost;
        } else {
            $stockCountingItem->total_diff = 0;
        }
    }

    /**
     * Handle the StockCountingItem "deleted" event.
     */
    public function deleted(StockCountingItem $stockCountingItem): void
    {
        //
    }

    /**
     * Handle the StockCountingItem "restored" event.
     */
    public function restored(StockCountingItem $stockCountingItem): void
    {
        //
    }

    /**
     * Handle the StockCountingItem "force deleted" event.
     */
    public function forceDeleted(StockCountingItem $stockCountingItem): void
    {
        //
    }
}
