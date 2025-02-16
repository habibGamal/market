<?php

namespace App\Observers;

use App\Models\StockItem;

class StockItemObserver
{
    /**
     * Handle the StockItem "created" event.
     */
    public function created(StockItem $stockItem): void
    {
        //
    }

    /**
     * Handle the StockItem "creating" event.
     */
    public function creating(StockItem $stockItem): void
    {
        $stockItem->warehouse_id = 1;
    }

    /**
     * Handle the StockItem "updated" event.
     */
    public function updated(StockItem $stockItem): void
    {
        //
    }

    /**
     * Handle the StockItem "deleted" event.
     */
    public function deleted(StockItem $stockItem): void
    {
        //
    }

    /**
     * Handle the StockItem "restored" event.
     */
    public function restored(StockItem $stockItem): void
    {
        //
    }

    /**
     * Handle the StockItem "force deleted" event.
     */
    public function forceDeleted(StockItem $stockItem): void
    {
        //
    }
}
