<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrderReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ReturnItemsChart extends ChartWidget
{

    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل مرتجعات العميل';

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

        $data = app(OrderReportService::class)->getCustomerReturnsChartData(
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
                    'borderColor' => '#ef4444',
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
