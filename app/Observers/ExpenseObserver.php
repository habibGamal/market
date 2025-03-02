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
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        if ($expense->approved_by) {
            \DB::transaction(function () use ($expense) {
                app(VaultService::class)->add($expense->value);
                app(WorkDayService::class)->update();
            });
        }
    }
}
