<?php

namespace App\Observers;

use App\Models\AccountantIssueNote;

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
}
