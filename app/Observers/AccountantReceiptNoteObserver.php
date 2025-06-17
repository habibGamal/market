<?php

namespace App\Observers;

use App\Models\AccountantReceiptNote;
use App\Services\AccountantReceiptNoteService;
use App\Services\VaultService;
use App\Services\WorkDayService;

class AccountantReceiptNoteObserver
{
    /**
     * Handle the AccountantReceiptNote "creating" event.
     */
    public function creating(AccountantReceiptNote $accountantReceiptNote): void
    {
        $accountantReceiptNote->officer_id = auth()->id();

        app(AccountantReceiptNoteService::class)->handle($accountantReceiptNote);
    }

    /**
     * Handle the AccountantReceiptNote "created" event.
     */
    public function created(AccountantReceiptNote $accountantReceiptNote): void
    {
        app(VaultService::class)->add($accountantReceiptNote->paid);
        app(WorkDayService::class)->update();

        // Update payment status of the related model if it's an IssueNote
        $relatedModel = $accountantReceiptNote->fromModel;
        if ($relatedModel instanceof \App\Models\IssueNote) {
            $relatedModel->updatePaymentStatus();
        }
    }

    /**
     * Handle the AccountantReceiptNote "deleted" event.
     */
    public function deleted(AccountantReceiptNote $accountantReceiptNote): void
    {
        app(VaultService::class)->remove($accountantReceiptNote->paid);
        app(WorkDayService::class)->update();

        // Update payment status of the related model if it's an IssueNote
        $relatedModel = $accountantReceiptNote->fromModel;
        if ($relatedModel instanceof \App\Models\IssueNote) {
            $relatedModel->updatePaymentStatus();
        }
    }
}
