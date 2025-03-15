<?php

namespace App\Filament\Widgets;

use App\Services\Reports\OrdersByAreasReportService;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class AreaReturnsChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'تفاصيل مرتجعات المنطقة';

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

        $data = app(OrdersByAreasReportService::class)->getAreaReturnsChartData(
            $this->record,
            $this->startDate,
            $this->endDate
        );

        return [
            'datasets' => [
                [
                    'label' => 'عدد المرتجعات',
                    'data' => $data['quantities'],
                    'borderColor' => '#EF4444',
                ],
                [
                    'label' => 'قيمة المرتجعات',
                    'data' => $data['totals'],
                    'borderColor' => '#F59E0B',
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
