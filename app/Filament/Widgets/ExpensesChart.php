<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ExpensesReportResource\Pages\ListExpensesReports;
use App\Services\Reports\ExpenseReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Livewire\Attributes\On;

class ExpensesChart extends ChartWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'إجمالي المصروفات';

    protected function getTablePage(): string
    {
        return ListExpensesReports::class;
    }

    protected function getData(): array
    {
        if (empty($this->tableFilters['report_filter'])) {
            $startDate = now()->startOfMonth();
            $endDate = now();
        } else {
            $startDate = $this->tableFilters['report_filter']['start_date'];
            $endDate = $this->tableFilters['report_filter']['end_date'];
        }

        $data = app(ExpenseReportService::class)->getExpensesTotalsByDate($startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => 'إجمالي المصروفات',
                    'data' => $data['totals'],
                    'backgroundColor' => 'rgba(220, 53, 69, 0.5)',
                    'borderColor' => '#dc3545',
                ],
                [
                    'label' => 'أذونات الصرف للمشتريات',
                    'data' => $data['purchase_issue_notes'],
                    'backgroundColor' => 'rgba(255, 193, 7, 0.5)',
                    'borderColor' => '#ffc107',
                ],
                [
                    'label' => 'أذونات القبض من مندوبين التسليم',
                    'data' => $data['driver_receipt_notes'],
                    'backgroundColor' => 'rgba(25, 135, 84, 0.5)',
                    'borderColor' => '#198754',
                ],
                [
                    'label' => 'أذونات القبض من مرتجعات المشتريات',
                    'data' => $data['purchase_return_receipt_notes'],
                    'backgroundColor' => 'rgba(13, 110, 253, 0.5)',
                    'borderColor' => '#0d6efd',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
