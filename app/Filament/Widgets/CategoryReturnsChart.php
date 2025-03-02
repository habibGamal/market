<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CategoryReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class CategoryReturnsChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'مرتجعات الفئة';

    public $record;
    public $start;
    public $end;

    #[On('updateChart')]
    public function updateValues($start, $end) {
        $this->start = $start;
        $this->end = $end;
    }

    protected function getData(): array
    {
        $start = $this->start ?? now()->startOfMonth()->format('Y-m-d');
        $end = $this->end ?? now()->format('Y-m-d H:i:s');
        $data = app(CategoryReportService::class)->getCategoryReturnsChartData($this->record, $start, $end);

        return [
            'datasets' => [
                [
                    'label' => 'كمية المرتجعات',
                    'data' => $data['quantities'],
                ],
                [
                    'label' => 'قيمة المرتجعات',
                    'data' => $data['values'],
                    'borderColor' => '#e31a1c',
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
