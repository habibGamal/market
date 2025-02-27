<?php

namespace App\Observers;

use App\Models\Expense;

class ExpenseObserver
{
    public function creating(Expense $expense): void
    {
        $expense->accountant_id = auth()->id();
    }
}
