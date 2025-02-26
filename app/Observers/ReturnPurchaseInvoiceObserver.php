<?php

namespace App\Observers;

use App\Models\ReturnPurchaseInvoice;
use App\Services\ReturnPurchaseInvoiceServices;

class ReturnPurchaseInvoiceObserver
{
    public function creating(ReturnPurchaseInvoice $returnPurchaseInvoice): void
    {
        $returnPurchaseInvoice->total = collect($returnPurchaseInvoice->items)->sum(function ($item) {
            return ($item['packets_quantity'] * $item['packet_cost']);
        });
        unset($returnPurchaseInvoice->items);
    }

    public function updating(ReturnPurchaseInvoice $returnPurchaseInvoice): void
    {
        $returnPurchaseInvoice->total = $returnPurchaseInvoice->getRelationValue('items')->sum('total');
        $returnPurchaseInvoice->offsetUnset('items');
    }

    public function updated(ReturnPurchaseInvoice $returnPurchaseInvoice): void
    {
        if ($returnPurchaseInvoice->isDirty('status') && $returnPurchaseInvoice->closed) {
            app(ReturnPurchaseInvoiceServices::class)->markItemsAsUnavailable($returnPurchaseInvoice);
        }
    }
}
