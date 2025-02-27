<?php

namespace App\Observers;

use App\Models\AccountantReceiptNote;
use App\Services\AccountantReceiptNoteService;

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
}
