<?php

namespace App\Observers;

use App\Models\Waste;
use App\Services\WasteServices;

class WasteObserver
{

    /**
     * Handle the Waste "creating" event.
     */
    public function creating(Waste $waste): void
    {
        $waste->total = collect($waste->items)->sum(function ($item) {
            $packetCost = $item['packet_cost'];
            $packetsQuantity = $item['packets_quantity'];
            $pieceQuantity = $item['piece_quantity'];
            $product = \App\Models\Product::find($item['product_id']);

            // Calculate total including both packets and individual pieces
                return ($packetsQuantity * $packetCost) + (($pieceQuantity / $product->packet_to_piece) * $packetCost);

        });
        unset($waste->items);
    }

    /**
     * Handle the Waste "updated" event.
     */
    public function updated(Waste $waste): void
    {

    }

    /**
     * Handle the Waste "updating" event.
     */
    public function updating(Waste $waste): void
    {
        $waste->total = $waste->getRelationValue('items')->sum('total');
        $waste->offsetUnset('items');
    }

    /**
     * Handle the Waste "deleted" event.
     */
    public function deleting(Waste $waste): void
    {
        app(WasteServices::class)->deleteWaste($waste);
    }

    /**
     * Handle the Waste "restored" event.
     */
    public function restored(Waste $waste): void
    {
        //
    }

    /**
     * Handle the Waste "force deleted" event.
     */
    public function forceDeleted(Waste $waste): void
    {
        //
    }
}
