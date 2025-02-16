<?php

namespace App\Observers;

use App\Models\PurchaseInvoice;

class PurchaseInvoiceObserver
{
    /**
     * Handle the PurchaseInvoice "created" event.
     */
    public function created(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "creating" event.
     */
    public function creating(PurchaseInvoice $purchaseInvoice): void
    {
        $purchaseInvoice->total = collect($purchaseInvoice->items)->sum(function ($item) {
            return $item['packets_quantity'] * $item['packet_cost'];
        });
        unset($purchaseInvoice->items);
    }

    /**
     * Handle the PurchaseInvoice "updated" event.
     */
    public function updated(PurchaseInvoice $purchaseInvoice): void
    {

    }

    /**
     * Handle the PurchaseInvoice "updating" event.
     */
    public function updating(PurchaseInvoice $purchaseInvoice): void
    {
        $purchaseInvoice->total = $purchaseInvoice->getRelationValue('items')->sum('total');
        // $purchaseInvoice->setOldItems($purchaseInvoice->items->toArray());
        // dd($purchaseInvoice->getRelationValue('items'));
        $purchaseInvoice->offsetUnset('items');
    }

    /**
     * Handle the PurchaseInvoice "deleted" event.
     */
    public function deleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "restored" event.
     */
    public function restored(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "force deleted" event.
     */
    public function forceDeleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }
}
