<?php

namespace App\Observers;

use App\Models\WasteItem;

class WasteItemObserver
{
    /**
     * Handle the WasteItem "created" event.
     */
    public function created(WasteItem $wasteItem): void
    {
        //
    }

    /**
     * Handle the WasteItem "updated" event.
     */
    public function updated(WasteItem $wasteItem): void
    {
        //
    }

    /**
     * Handle the WasteItem "saving" event.
     */
    public function saving(WasteItem $wasteItem): void
    {
        // Calculate the total based on packets and pieces
        if ($wasteItem->product && $wasteItem->product->packet_to_piece > 0) {
            $wasteItem->total = ($wasteItem->packets_quantity * $wasteItem->packet_cost) +
                               (($wasteItem->piece_quantity / $wasteItem->product->packet_to_piece) * $wasteItem->packet_cost);
        } else {
            $wasteItem->total = $wasteItem->packets_quantity * $wasteItem->packet_cost;
        }
    }

    /**
     * Handle the WasteItem "deleted" event.
     */
    public function deleted(WasteItem $wasteItem): void
    {
        //
    }

    /**
     * Handle the WasteItem "restored" event.
     */
    public function restored(WasteItem $wasteItem): void
    {
        //
    }

    /**
     * Handle the WasteItem "force deleted" event.
     */
    public function forceDeleted(WasteItem $wasteItem): void
    {
        //
    }
}
