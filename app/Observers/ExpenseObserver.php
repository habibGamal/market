<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\VaultService;
use App\Services\WorkDayService;

class ExpenseObserver
{
    public function creating(Expense $expense): void
    {
        $expense->accountant_id = auth()->id();
    }

    /**
     * Handle the Expense "created" event.
     */
    public function updating(Expense $expense): void
    {
        if ($expense->isDirty('approved_by') && $expense->approved_by) {
            app(VaultService::class)->remove($expense->value);
            app(WorkDayService::class)->update();
        }
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        if ($expense->approved_by) {
            app(VaultService::class)->add($expense->value);
            app(WorkDayService::class)->update();
        }
    }
}
