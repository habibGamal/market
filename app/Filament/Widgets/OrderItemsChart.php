<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;
use App\Services\Reports\OrdersByCustomersReportService;

class OrderItemsChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل طلبات العميل';

    public $record;
    public $startDate;
    public $endDate;

    #[On('updateChart')]
    public function updateChart($start = null, $end = null): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
    }

    protected function getData(): array
    {
        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth();
            $this->endDate = now();
        }

        $data = app(OrdersByCustomersReportService::class)->getCustomerOrdersChartData(
            $this->record,
            $this->startDate,
            $this->endDate
        );

        return [
            'datasets' => [
                [
                    'label' => 'الكمية',
                    'data' => $data['quantities'],
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'القيمة',
                    'data' => $data['totals'],
                    'borderColor' => '#22c55e',
                ],
                [
                    'label' => 'الربح',
                    'data' => $data['profits'],
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
