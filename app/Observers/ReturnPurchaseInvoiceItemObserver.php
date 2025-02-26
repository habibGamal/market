<?php

namespace App\Observers;

use App\Models\ReturnPurchaseInvoiceItem;

class ReturnPurchaseInvoiceItemObserver
{
    public function saving(ReturnPurchaseInvoiceItem $item): void
    {
        $item->total = $item->packets_quantity * $item->packet_cost;
    }
}
