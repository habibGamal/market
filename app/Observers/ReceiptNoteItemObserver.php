<?php

namespace App\Observers;

use App\Models\ReceiptNoteItem;

class ReceiptNoteItemObserver
{
    /**
     * Handle the ReceiptNoteItem "created" event.
     */
    public function created(ReceiptNoteItem $receiptNoteItem): void
    {
        //
    }

    /**
     * Handle the ReceiptNoteItem "updated" event.
     */
    public function updated(ReceiptNoteItem $receiptNoteItem): void
    {
        //
    }

    /**
     * Handle the ReceiptNoteItem "updating" event.
     */
    public function updating(ReceiptNoteItem $receiptNoteItem): void
    {
        $receiptNoteItem->total = $receiptNoteItem->total_quantity_by_packet * $receiptNoteItem->packet_cost;
    }

    /**
     * Handle the ReceiptNoteItem "deleted" event.
     */
    public function deleted(ReceiptNoteItem $receiptNoteItem): void
    {
        //
    }

    /**
     * Handle the ReceiptNoteItem "restored" event.
     */
    public function restored(ReceiptNoteItem $receiptNoteItem): void
    {
        //
    }

    /**
     * Handle the ReceiptNoteItem "force deleted" event.
     */
    public function forceDeleted(ReceiptNoteItem $receiptNoteItem): void
    {
        //
    }
}
