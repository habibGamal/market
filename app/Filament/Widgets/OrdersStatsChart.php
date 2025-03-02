<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrderReportService;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Livewire\Attributes\On;

class OrdersStatsChart extends ChartWidget
{

    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'إحصائيات الطلبات';
    protected function getData(): array
    {
        if (empty($this->tableFilters['report_filter'])) {
            $startDate = now()->startOfMonth();
            $endDate = now();
        } else {
            $startDate = Carbon::parse($this->tableFilters['report_filter']['start_date']);
            $endDate = Carbon::parse($this->tableFilters['report_filter']['end_date']);
        }
        $data = app(OrderReportService::class)->getOrdersChartData($startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $data['count'],
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'المبيعات',
                    'data' => $data['sales'],
                    'borderColor' => '#22c55e',
                ],
                [
                    'label' => 'المرتجعات',
                    'data' => $data['returns'],
                    'borderColor' => '#ef4444',
                ],
                [
                    'label' => 'الملغية',
                    'data' => $data['cancelled'],
                    'borderColor' => '#f97316',
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
