<?php

namespace App\Services\Reports;

use App\Enums\InvoiceStatus;
use App\Models\PurchaseInvoice;
use App\Models\ReturnPurchaseInvoice;
use App\Models\Waste;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InvoiceReportService
{
    /**
     * Get filtered query for summary reporting
     */
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        // This is a placeholder query that will be replaced with actual implementation
        // since we're not tracking invoices by a specific entity like customers
        return $query;
    }

    /**
     * Get purchase invoice statistics
     */
    public function getPurchaseStats($start = null, $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now();

        $totalPurchases = PurchaseInvoice::whereBetween('created_at', [$start, $end])->sum('total');

        // Purchases that have receipt notes (received) - sum of receipt notes totals
        $totalReceived = PurchaseInvoice::whereBetween('purchase_invoices.created_at', [$start, $end])
            ->join('receipt_notes', 'purchase_invoices.receipt_note_id', '=', 'receipt_notes.id')
            ->sum('receipt_notes.total');

        // Purchases that don't have receipt notes (not received)
        $totalNotReceived = PurchaseInvoice::whereBetween('created_at', [$start, $end])
            ->whereNull('receipt_note_id')
            ->sum('total');

        return [
            'total_purchases' => $totalPurchases,
            'total_received' => $totalReceived,
            'total_not_received' => $totalNotReceived,
        ];
    }

    /**
     * Get return purchase invoice statistics
     */
    public function getReturnStats($start = null, $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now();

        $totalReturns = ReturnPurchaseInvoice::whereBetween('created_at', [$start, $end])->sum('total');

        // Returns that have issue notes (issued)
        $totalIssued = ReturnPurchaseInvoice::whereBetween('created_at', [$start, $end])
            ->whereNotNull('issue_note_id')
            ->sum('total');

        // Returns that don't have issue notes (not issued)
        $totalNotIssued = ReturnPurchaseInvoice::whereBetween('created_at', [$start, $end])
            ->whereNull('issue_note_id')
            ->sum('total');

        return [
            'total_returns' => $totalReturns,
            'total_issued' => $totalIssued,
            'total_not_issued' => $totalNotIssued,
        ];
    }

    /**
     * Get waste invoice statistics
     */
    public function getWasteStats($start = null, $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now();

        $totalWaste = Waste::whereBetween('created_at', [$start, $end])->sum('total');

        // Waste that have issue notes (issued)
        $totalIssued = Waste::whereBetween('created_at', [$start, $end])
            ->whereNotNull('issue_note_id')
            ->sum('total');

        // Waste that don't have issue notes (not issued)
        $totalNotIssued = Waste::whereBetween('created_at', [$start, $end])
            ->whereNull('issue_note_id')
            ->sum('total');

        return [
            'total_waste' => $totalWaste,
            'total_issued' => $totalIssued,
            'total_not_issued' => $totalNotIssued,
        ];
    }

    /**
     * Get invoice chart data for purchases, returns and waste
     */
    public function getInvoiceChartData($start = null, $end = null, int $days = 30): array
    {
        $start = $start ? Carbon::parse($start) : now()->subDays($days);
        $end = $end ? Carbon::parse($end) : now();

        // Purchase invoices trend - get the totals from receipt notes
        $purchaseTrend = Trend::query(
            PurchaseInvoice::query()
                ->whereNotNull('receipt_note_id')
                ->join('receipt_notes', 'purchase_invoices.receipt_note_id', '=', 'receipt_notes.id')
        )
            ->between($start, $end)
            ->dateColumn('purchase_invoices.created_at')
            ->perDay()
            ->sum('receipt_notes.total');

        // Return purchase invoices trend
        $returnTrend = Trend::model(ReturnPurchaseInvoice::class)
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        // Waste invoices trend
        $wasteTrend = Trend::model(Waste::class)
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        return [
            'labels' => $purchaseTrend->map(fn(TrendValue $value) => $value->date),
            'purchases' => $purchaseTrend->map(fn(TrendValue $value) => $value->aggregate),
            'returns' => $returnTrend->map(fn(TrendValue $value) => $value->aggregate),
            'waste' => $wasteTrend->map(fn(TrendValue $value) => $value->aggregate),
        ];
    }

    /**
     * Get all invoice statistics in a single call
     */
    public function getAllStats($start = null, $end = null): array
    {
        return [
            'purchases' => $this->getPurchaseStats($start, $end),
            'returns' => $this->getReturnStats($start, $end),
            'waste' => $this->getWasteStats($start, $end),
        ];
    }
}
