<?php

namespace App\Filament\Widgets;

use App\Services\Reports\RevenueReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class RevenueChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'تحليل الإيرادات والأرباح';

    public array $filterFormData = [];

    #[On('updateWidgets')]
    public function updateFilterFormData($filterFormData)
    {
        $this->filterFormData = $filterFormData;
    }

    protected function getData(): array
    {
        // Get parameters from passed state or use defaults
        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;
        $period = $this->filterFormData['period'] ?? '30';

        // Get chart data from service
        $data = app(RevenueReportService::class)->getRevenueChartData($startDate, $endDate);

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات',
                    'data' => $data['sales'],
                    'borderColor' => '#3b82f6', // blue-500
                    'backgroundColor' => '#93c5fd', // blue-300
                    'fill' => false,
                ],
                [
                    'label' => 'المرتجعات',
                    'data' => $data['returns'],
                    'borderColor' => '#ef4444', // red-500
                    'backgroundColor' => '#fca5a5', // red-300
                    'fill' => false,
                ],
                [
                    'label' => 'المصروفات',
                    'data' => $data['expenses'],
                    'borderColor' => '#f97316', // orange-500
                    'backgroundColor' => '#fdba74', // orange-300
                    'fill' => false,
                ],
                [
                    'label' => 'صافي المبيعات',
                    'data' => $data['net_sales'],
                    'borderColor' => '#0891b2', // cyan-600
                    'backgroundColor' => '#67e8f9', // cyan-300
                    'fill' => false,
                ],
                [
                    'label' => 'مجمل الربح',
                    'data' => $data['gross_profits'],
                    'borderColor' => '#16a34a', // green-600
                    'backgroundColor' => '#86efac', // green-300
                    'fill' => false,
                ],
                [
                    'label' => 'صافي الربح',
                    'data' => $data['net_profits'],
                    'borderColor' => '#7c3aed', // violet-600
                    'backgroundColor' => '#c4b5fd', // violet-300
                    'fill' => false,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => value + " جنيه"',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => '(context) => context.dataset.label + ": " + context.parsed.y + " جنيه"',
                    ],
                ],
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'elements' => [
                'line' => [
                    'tension' => 0.1, // slightly curved (0 = no curve)
                ],
                'point' => [
                    'radius' => 3, // smaller points
                    'hoverRadius' => 5,
                ],
            ],
        ];
    }
}
