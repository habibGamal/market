<?php

namespace App\Filament\Widgets;

use App\Models\AssetEntry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CapitalPartnersPieChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'حصص الشركاء في رأس المال';

    protected function getData(): array
    {
        $partners = AssetEntry::query()
            ->select('notes', DB::raw('SUM(value) as total_value'))
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->groupBy('notes')
            ->orderByDesc('total_value')
            ->get();

        $grandTotal = $partners->sum('total_value');

        if ($partners->isEmpty() || $grandTotal <= 0) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $colors = [
            '#3b82f6', // blue
            '#ef4444', // red
            '#10b981', // emerald
            '#f59e0b', // amber
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#06b6d4', // cyan
            '#f97316', // orange
            '#14b8a6', // teal
            '#6366f1', // indigo
            '#84cc16', // lime
            '#e11d48', // rose
        ];

        $labels = $partners->map(function ($partner, $index) use ($grandTotal) {
            $percentage = round(($partner->total_value / $grandTotal) * 100, 1);

            return $partner->notes . ' - ' . number_format($partner->total_value, 2) . ' ج.م (' . $percentage . '%)';
        })->toArray();

        $backgroundColors = $partners->map(function ($partner, $index) use ($colors) {
            return $colors[$index % count($colors)];
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'حصص الشركاء',
                    'data' => $partners->pluck('total_value')->toArray(),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'font' => [
                            'size' => 13,
                        ],
                        'padding' => 15,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => '(context) => {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label || context.parsed + " ج.م (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
