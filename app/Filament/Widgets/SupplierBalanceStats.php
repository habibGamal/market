<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class SupplierBalanceStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public ?int $supplierId = null;

    public array $filterFormData = [];

    protected function getStats(): array
    {
        if (!$this->supplierId) {
            return [];
        }

        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;

        $receiptTotal = $this->getReceiptTotal($startDate, $endDate);
        $receiptPaid = $this->getReceiptPaid($startDate, $endDate);
        $issueTotal = $this->getIssueTotal($startDate, $endDate);
        $issuePaid = $this->getIssuePaid($startDate, $endDate);
        $balance = $receiptTotal - $receiptPaid - $issueTotal + $issuePaid;

        return [
            Stat::make('اجمالي الفواتير', number_format($receiptTotal, 2) . ' جنية')
                ->description('اجمالي فواتير المشتريات')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('المدفوع من الفواتير', number_format($receiptPaid, 2) . ' جنية')
                ->description('المبالغ المدفوعة من فواتير المشتريات')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('اجمالي مرتجعات المشتريات', number_format($issueTotal, 2) . ' جنية')
                ->description('اجمالي فواتير مرتجعات المشتريات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('warning'),
            Stat::make('المدفوع من مرتجعات المشتريات', number_format($issuePaid, 2) . ' جنية')
                ->description('المبالغ المدفوعة من مرتجعات المشتريات')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
            Stat::make('المحصلة', number_format($balance, 2) . ' جنية')
                ->description('الرصيد الإجمالي للمورد')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($balance >= 0 ? 'primary' : 'danger'),
        ];
    }

    #[On('updateSupplierStats')]
    public function updateSupplierStats(array $filterFormData): void
    {
        $this->filterFormData = $filterFormData;
    }

    private function getReceiptTotal(?string $startDate, ?string $endDate): float
    {
        $query = DB::table('purchase_invoices')
            ->join('receipt_notes', 'receipt_notes.id', '=', 'purchase_invoices.receipt_note_id')
            ->where('purchase_invoices.supplier_id', $this->supplierId);

        if ($startDate) {
            $query->where('receipt_notes.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('receipt_notes.created_at', '<=', $endDate);
        }

        return (float) $query->sum('receipt_notes.total');
    }

    private function getReceiptPaid(?string $startDate, ?string $endDate): float
    {
        $query = DB::table('purchase_invoices')
            ->join('receipt_notes', 'receipt_notes.id', '=', 'purchase_invoices.receipt_note_id')
            ->join('accountant_issue_notes', function ($join) {
                $join->on('accountant_issue_notes.for_model_id', '=', 'receipt_notes.id')
                    ->where('accountant_issue_notes.for_model_type', '=', 'App\\Models\\ReceiptNote');
            })
            ->where('purchase_invoices.supplier_id', $this->supplierId);

        if ($startDate) {
            $query->where('accountant_issue_notes.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('accountant_issue_notes.created_at', '<=', $endDate);
        }

        return (float) $query->sum('accountant_issue_notes.paid');
    }

    private function getIssueTotal(?string $startDate, ?string $endDate): float
    {
        $query = DB::table('return_purchase_invoices')
            ->join('issue_notes', 'issue_notes.id', '=', 'return_purchase_invoices.issue_note_id')
            ->where('return_purchase_invoices.supplier_id', $this->supplierId);

        if ($startDate) {
            $query->where('issue_notes.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('issue_notes.created_at', '<=', $endDate);
        }

        return (float) $query->sum('issue_notes.total');
    }

    private function getIssuePaid(?string $startDate, ?string $endDate): float
    {
        $query = DB::table('return_purchase_invoices')
            ->join('issue_notes', 'issue_notes.id', '=', 'return_purchase_invoices.issue_note_id')
            ->join('accountant_receipt_notes', function ($join) {
                $join->on('accountant_receipt_notes.from_model_id', '=', 'issue_notes.id')
                    ->where('accountant_receipt_notes.from_model_type', '=', 'App\\Models\\IssueNote');
            })
            ->where('return_purchase_invoices.supplier_id', $this->supplierId);

        if ($startDate) {
            $query->where('accountant_receipt_notes.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('accountant_receipt_notes.created_at', '<=', $endDate);
        }

        return (float) $query->sum('accountant_receipt_notes.paid');
    }
}
