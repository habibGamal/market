<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrdersByAreasReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class AreaOrdersChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل طلبات المنطقة';

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

        $data = app(OrdersByAreasReportService::class)->getAreaOrdersChartData(
            $this->record,
            $this->startDate,
            $this->endDate
        );

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $data['quantities'],
                    'borderColor' => '#10B981',
                ],
                [
                    'label' => 'قيمة المبيعات',
                    'data' => $data['totals'],
                    'borderColor' => '#3B82F6',
                ],
                [
                    'label' => 'الأرباح',
                    'data' => $data['profits'],
                    'borderColor' => '#8B5CF6',
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
