<?php

namespace App\Observers;

use App\Models\PurchaseInvoiceItem;

class PurchaseInvoiceItemObserver
{
    /**
     * Handle the PurchaseInvoiceItem "created" event.
     */
    public function created(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "updated" event.
     */
    public function updated(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "saving" event.
     */
    public function saving(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        $purchaseInvoiceItem->total = $purchaseInvoiceItem->packets_quantity * $purchaseInvoiceItem->packet_cost;
    }

    /**
     * Handle the PurchaseInvoiceItem "deleted" event.
     */
    public function deleted(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "restored" event.
     */
    public function restored(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "force deleted" event.
     */
    public function forceDeleted(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }
}
