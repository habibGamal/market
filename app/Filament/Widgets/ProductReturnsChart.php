<?php

namespace App\Filament\Widgets;

use App\Models\ReturnOrderItem;
use App\Services\Reports\ProductReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ProductReturnsChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'مرتجعات المنتجات';


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
        $data = app(ProductReportService::class)->getProductReturnsChartData($this->record, $start, $end);

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
