<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Enums\PaymentStatus;
use App\Models\ReceiptNote;
use App\Services\ReceiptNoteServices;

class ReceiptNoteObserver
{
    /**
     * Handle the ReceiptNote "creating" event.
     */
    public function creating(ReceiptNote $receiptNote): void
    {
        // Set default payment status
        if (!$receiptNote->payment_status) {
            $receiptNote->payment_status = PaymentStatus::UNPAID;
        }
    }

    /**
     * Handle the ReceiptNote "created" event.
     */
    public function created(ReceiptNote $receiptNote): void
    {
        //
    }

    /**
     * Handle the ReceiptNote "updated" event.
     */
    public function updated(ReceiptNote $receiptNote): void
    {

    }

    /**
     * Handle the ReceiptNote "updating" event.
     */
    public function updating(ReceiptNote $receiptNote): void
    {
        // we use getRelationValue to get the related items not the items attribute
        $receiptNote->total = $receiptNote->getRelationValue('items')->sum('total');

        // this unset items from the model attributes
        // as we dehydrate it in the form to trigger the observer
        $receiptNote->offsetUnset('items');

        if ($receiptNote->isDirty('status') && $receiptNote->status === InvoiceStatus::CLOSED) {
            $services = app(ReceiptNoteServices::class);
            $services->toStock($receiptNote);
            if ($receiptNote->note_type === ReceiptNoteType::RETURN_ORDERS)
                $services->removeQuantitiesFromDriverProducts($receiptNote);
        }
    }

    /**
     * Handle the ReceiptNote "deleted" event.
     */
    public function deleted(ReceiptNote $receiptNote): void
    {
        //
    }

    /**
     * Handle the ReceiptNote "restored" event.
     */
    public function restored(ReceiptNote $receiptNote): void
    {
        //
    }

    /**
     * Handle the ReceiptNote "force deleted" event.
     */
    public function forceDeleted(ReceiptNote $receiptNote): void
    {
        //
    }
}
