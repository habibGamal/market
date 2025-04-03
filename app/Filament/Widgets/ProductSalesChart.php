<?php

namespace App\Filament\Widgets;

use App\Services\Reports\ProductReportService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ProductSalesChart extends ChartWidget
{

    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'مبيعات المنتجات';

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
        $data = app(ProductReportService::class)->getProductSalesChartData($this->record, $start, $end);
        $datasets = [
            [
                'label' => 'كمية المبيعات',
                'data' => $data['quantities'],
            ],
            [
                'label' => 'قيمة المبيعات',
                'data' => $data['values'],
                'borderColor' => '#00441b',
            ],
        ];

        // Check if user has permission to view profits
        if (auth()->user()->can('view_profits_product')) {
            $datasets[] = [
                'label' => 'ارباح المبيعات',
                'data' => $data['profits'],
                'borderColor' => '#74c476',
            ];
        }
        return [
            'datasets' => $datasets,
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
