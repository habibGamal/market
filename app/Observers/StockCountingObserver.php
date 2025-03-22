<?php

namespace App\Observers;

use App\Models\StockCounting;
use App\Services\StockCountingServices;

class StockCountingObserver
{
    /**
     * Handle the StockCounting "creating" event.
     */
    public function creating(StockCounting $stockCounting): void
    {
        $stockCounting->total_diff = collect($stockCounting->items)->sum(function ($item) {
            $product = \App\Models\Product::find($item['product_id']);
            $oldTotalInPieces = ($item['old_packets_quantity'] * $product->packet_to_piece) + $item['old_piece_quantity'];
            $newTotalInPieces = ($item['new_packets_quantity'] * $product->packet_to_piece) + $item['new_piece_quantity'];
            $diffInPieces = $newTotalInPieces - $oldTotalInPieces;

            return ($diffInPieces / $product->packet_to_piece) * $item['packet_cost'];
        });
        unset($stockCounting->items);
    }

    /**
     * Handle the StockCounting "updated" event.
     */
    public function updated(StockCounting $stockCounting): void
    {
        //
    }

    /**
     * Handle the StockCounting "updating" event.
     */
    public function updating(StockCounting $stockCounting): void
    {
        $stockCounting->total_diff = $stockCounting->getRelationValue('items')->sum('total_diff');
        $stockCounting->offsetUnset('items');
    }

    /**
     * Handle the StockCounting "deleting" event.
     */
    public function deleting(StockCounting $stockCounting): void
    {
        if ($stockCounting->closed) {
            throw new \Exception('لا يمكن حذف عملية جرد مغلقة');
        }
    }

    /**
     * Handle the StockCounting "restored" event.
     */
    public function restored(StockCounting $stockCounting): void
    {
        //
    }

    /**
     * Handle the StockCounting "force deleted" event.
     */
    public function forceDeleted(StockCounting $stockCounting): void
    {
        //
    }
}
