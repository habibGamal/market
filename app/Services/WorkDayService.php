<?php

namespace App\Services;

use App\Models\AssetEntry;
use App\Models\WorkDay;
use App\Models\AccountantIssueNote;
use App\Models\AccountantReceiptNote;
use App\Models\Driver;
use App\Models\IssueNote;
use App\Models\ReceiptNote;
use App\Models\Expense;
use App\Models\Asset;
use Illuminate\Support\Carbon;

class WorkDayService
{
    public function getToday(): WorkDay
    {
        return WorkDay::firstOrCreate(
            ['day' => Carbon::today()],
            [
                'start_day' => $this->getPreviousDayTotal() ?? 0,
                'total_purchase' => 0,
                'total_sales' => 0,
                'total_expenses' => 0,
                'total_assets' => 0,
                'total_purchase_returnes' => 0,
                'total_day' => 0,
            ]
        );
    }

    protected function getPreviousDayTotal(): ?float
    {
        return WorkDay::where('day', '<', Carbon::today())
            ->orderBy('day', 'desc')
            ->value('total_day');
    }

    public function update(): WorkDay
    {
        $workDay = $this->getToday();
        $today = Carbon::today();

        // Get total purchases (sum of AccountantIssueNotes that are for ReceiptNotes of type purchase)
        $totalPurchase = AccountantIssueNote::whereDate('created_at', $today)
            ->whereHasMorph('forModel', [ReceiptNote::class])
            ->sum('paid');

        // Get total sales (sum of AccountantReceiptNotes from drivers)
        $totalSales = AccountantReceiptNote::whereDate('created_at', $today)
            ->whereHasMorph('fromModel', [Driver::class])
            ->sum('paid');

        // Get total purchase returns (sum of AccountantReceiptNotes from IssueNotes of type return purchases)
        $totalPurchaseReturns = AccountantReceiptNote::whereDate('created_at', $today)
            ->whereHasMorph('fromModel', [IssueNote::class])
            ->sum('paid');

        // Get total approved expenses
        $totalExpenses = Expense::whereDate('created_at', $today)
            ->whereNotNull('approved_by')
            ->sum('value');

        // Get total assets purchased today
        $totalAssets = AssetEntry::whereDate('created_at', $today)
            ->sum('value');

        // Calculate total day
        $totalDay = $workDay->start_day + $totalAssets + $totalSales - $totalPurchase - $totalExpenses + $totalPurchaseReturns;

        // Update work day
        $workDay->update([
            'total_purchase' => $totalPurchase,
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'total_purchase_returnes' => $totalPurchaseReturns,
            'total_assets' => $totalAssets,
            'total_day' => $totalDay,
        ]);

        return $workDay;
    }
}
