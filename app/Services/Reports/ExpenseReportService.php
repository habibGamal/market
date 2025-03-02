<?php

namespace App\Services\Reports;

use App\Models\AccountantIssueNote;
use App\Models\AccountantReceiptNote;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\WorkDay;
use App\Models\PurchaseInvoice;
use App\Models\Driver;
use App\Models\ReturnPurchaseInvoice;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseReportService
{
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : null;
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : null;

        // Get total expenses for each expense type in the given period
        return $query->select([
                'expense_types.id',
                'expense_types.name',
                DB::raw('SUM(expenses.value) as total_value'),
                DB::raw('COUNT(expenses.id) as expenses_count'),
                DB::raw('SUM(CASE WHEN expenses.approved_by IS NOT NULL THEN expenses.value ELSE 0 END) as approved_value'),
                DB::raw('SUM(CASE WHEN expenses.approved_by IS NULL THEN expenses.value ELSE 0 END) as not_approved_value')
            ])
            ->leftJoin('expenses', 'expense_types.id', '=', 'expenses.expense_type_id')
            ->when($startDate, fn($query) => $query->where('expenses.created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('expenses.created_at', '<=', $endDate))
            ->groupBy('expense_types.id', 'expense_types.name');
    }

    public function getExpensesTotalsByDate($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $workDaysData = Trend::query(WorkDay::query())
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('total_expenses');

        $purchaseIssueData = Trend::query(AccountantIssueNote::query()->where('for_model_type', PurchaseInvoice::class))
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('paid');

        $driverReceiptData = Trend::query(AccountantReceiptNote::query()->where('from_model_type', Driver::class))
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('paid');

        $purchaseReturnReceiptData = Trend::query(AccountantReceiptNote::query()->where('from_model_type', ReturnPurchaseInvoice::class))
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('paid');

        return [
            'labels' => $workDaysData->map(fn(TrendValue $value) => $value->date),
            'totals' => $workDaysData->map(fn(TrendValue $value) => $value->aggregate ?: 0),
            'purchase_issue_notes' => $purchaseIssueData->map(fn(TrendValue $value) => $value->aggregate ?: 0),
            'driver_receipt_notes' => $driverReceiptData->map(fn(TrendValue $value) => $value->aggregate ?: 0),
            'purchase_return_receipt_notes' => $purchaseReturnReceiptData->map(fn(TrendValue $value) => $value->aggregate ?: 0),
        ];
    }

    public function getTotalApprovedExpenses($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return Expense::query()
            ->whereNotNull('approved_by')
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->sum('value');
    }

    public function getTotalNotApprovedExpenses($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return Expense::query()
            ->whereNull('approved_by')
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->sum('value');
    }

    public function getTotalPurchaseAccountantIssueNotes($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return AccountantIssueNote::query()
            ->where('for_model_type', PurchaseInvoice::class)
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->sum('paid');
    }

    public function getTotalDriverAccountantReceiptNotes($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return AccountantReceiptNote::query()
            ->where('from_model_type', Driver::class)
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->sum('paid');
    }

    public function getTotalPurchaseReturnAccountantReceiptNotes($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        return AccountantReceiptNote::query()
            ->where('from_model_type', ReturnPurchaseInvoice::class)
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->sum('paid');
    }
}
