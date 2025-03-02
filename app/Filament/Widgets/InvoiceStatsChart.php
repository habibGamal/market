<?php

namespace App\Filament\Widgets;

use App\Services\Reports\InvoiceReportService;
use App\Traits\ReportsFilter;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class InvoiceStatsChart extends ChartWidget
{
    use ReportsFilter;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'إجمالي الفواتير';

    protected function getData(): array
    {
        // Get parameters from passed state or use defaults
        $startDate = $this->filterFormData['start_date'] ?? now()->startOfMonth();
        $endDate = $this->filterFormData['end_date'] ?? now();
        $period = $this->filterFormData['period'] ?? '30';

        // Calculate days for the chart
        $days = 30;
        if ($period && $period !== static::PERIOD_CUSTOM) {
            $days = (int) $period;
        }

        $chartData = app(InvoiceReportService::class)->getInvoiceChartData($startDate, $endDate, $days);

        return [
            'datasets' => [
                [
                    'label' => 'المشتريات',
                    'data' => $chartData['purchases'],
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                ],
                [
                    'label' => 'المرتجعات',
                    'data' => $chartData['returns'],
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                ],
                [
                    'label' => 'الهالك',
                    'data' => $chartData['waste'],
                    'borderColor' => '#FFCE56',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                ],
            ],
            'labels' => $chartData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public array $filterFormData = [];

    #[On('updateWidgets')]
    public function updateWidgets(array $filterFormData): void
    {
        $this->filterFormData = $filterFormData;
    }

    protected static ?array $options = [
        'scales' => [
            'y' => [
                'beginAtZero' => true,
            ],
        ],
    ];
}
