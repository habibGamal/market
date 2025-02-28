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
        $accountantIssueNote->paid = $accountantIssueNote->for_model_type::find($accountantIssueNote->for_model_id)->total;
    }

    /**
     * Handle the AccountantIssueNote "created" event.
     */
    public function created(AccountantIssueNote $accountantIssueNote): void
    {
        app(VaultService::class)->remove($accountantIssueNote->paid);
        app(WorkDayService::class)->update();
    }

    /**
     * Handle the AccountantIssueNote "deleted" event.
     */
    public function deleted(AccountantIssueNote $accountantIssueNote): void
    {
        app(VaultService::class)->add($accountantIssueNote->paid);
        app(WorkDayService::class)->update();
    }
}
