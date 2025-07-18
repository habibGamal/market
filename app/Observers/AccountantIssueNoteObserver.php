<?php

namespace App\Observers;

use App\Models\AccountantIssueNote;
use App\Services\VaultService;
use App\Services\WorkDayService;

class AccountantIssueNoteObserver
{
    /**
     * Handle the AccountantIssueNote "creating" event.
     */
    public function creating(AccountantIssueNote $accountantIssueNote): void
    {
        $accountantIssueNote->officer_id = auth()->id();

        $relatedModel = $accountantIssueNote->for_model_type::find($accountantIssueNote->for_model_id);
        // If paid amount is not set, default to the remaining amount
        if (!$accountantIssueNote->paid) {
            $accountantIssueNote->paid = $relatedModel->remaining_amount ?? $relatedModel->total;
        }

        // Validate that payment amount is positive
        if ($accountantIssueNote->paid <= 0) {
            throw new \InvalidArgumentException('المبلغ المدفوع يجب أن يكون أكبر من صفر');
        }

        // Validate that payment doesn't exceed remaining amount
        if ((float) $accountantIssueNote->paid > (float) $relatedModel->remaining_amount) {
            throw new \InvalidArgumentException('المبلغ المدفوع لا يمكن أن يتجاوز المبلغ المتبقي (' . number_format($relatedModel->remaining_amount, 2) . ' جنيه)');
        }
    }

    /**
     * Handle the AccountantIssueNote "created" event.
     */
    public function created(AccountantIssueNote $accountantIssueNote): void
    {
        app(VaultService::class)->remove($accountantIssueNote->paid);
        app(WorkDayService::class)->update();

        // Update payment status of the related model if it's a ReceiptNote
        $relatedModel = $accountantIssueNote->forModel;
        if ($relatedModel instanceof \App\Models\ReceiptNote) {
            $relatedModel->updatePaymentStatus();
        }
    }

    /**
     * Handle the AccountantIssueNote "deleted" event.
     */
    public function deleted(AccountantIssueNote $accountantIssueNote): void
    {
        app(VaultService::class)->add($accountantIssueNote->paid);
        app(WorkDayService::class)->update();

        // Update payment status of the related model if it's a ReceiptNote
        $relatedModel = $accountantIssueNote->forModel;
        if ($relatedModel instanceof \App\Models\ReceiptNote) {
            $relatedModel->updatePaymentStatus();
        }
    }
}
