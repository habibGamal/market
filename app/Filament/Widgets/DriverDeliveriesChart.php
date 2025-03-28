<?php

namespace App\Filament\Widgets;

use App\Services\Reports\DriverReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class DriverDeliveriesChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل التسليمات';

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

        $data = app(DriverReportService::class)->getDeliveriesChartData(
            $this->record,
            $this->startDate,
            $this->endDate
        );

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $data['quantities'],
                ],
                [
                    'label' => 'القيم',
                    'data' => $data['totals'],
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
