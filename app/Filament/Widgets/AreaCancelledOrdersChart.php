<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrdersByAreasReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class AreaCancelledOrdersChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل الطلبات الملغية';

    public $record;
    public $startDate;
    public $endDate;

    protected static ?string $maxHeight = '300px';

    #[On('updateChart')]
    public function updateChart($start = null, $end = null): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
    }

    protected function getData(): array
    {
        if (empty($this->record)) {
            return [];
        }

        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth();
            $this->endDate = now();
        }

        $data = app(OrdersByAreasReportService::class)->getAreaCancelledOrdersChartData(
            $this->record,
            $this->startDate,
            $this->endDate
        );

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات الملغية',
                    'data' => $data['quantities'],
                    'borderColor' => '#6b7280',
                ],
                [
                    'label' => 'قيمة الطلبات الملغية',
                    'data' => $data['totals'],
                    'borderColor' => '#94a3b8',
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
