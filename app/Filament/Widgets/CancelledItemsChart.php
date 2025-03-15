<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrdersByCustomersReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Livewire\Attributes\On;

class CancelledItemsChart extends ChartWidget
{

    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل الطلبات الملغية للعميل';

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

        $data = app(OrdersByCustomersReportService::class)->getCustomerCancelsChartData(
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
