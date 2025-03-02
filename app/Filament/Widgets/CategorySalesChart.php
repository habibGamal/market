<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CategoryReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class CategorySalesChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'مبيعات الفئة';

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
        $data = app(CategoryReportService::class)->getCategorySalesChartData($this->record, $start, $end);

        return [
            'datasets' => [
                [
                    'label' => 'كمية المبيعات',
                    'data' => $data['quantities'],
                ],
                [
                    'label' => 'قيمة المبيعات',
                    'data' => $data['values'],
                    'borderColor' => '#00441b',
                ],
                [
                    'label' => 'ارباح المبيعات',
                    'data' => $data['profits'],
                    'borderColor' => '#74c476',
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
