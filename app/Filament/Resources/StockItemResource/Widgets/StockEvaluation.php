<?php

namespace App\Filament\Resources\StockItemResource\Widgets;

use App\Filament\Resources\StockItemResource\Pages\ListStockItems;
use App\Models\Product;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;


class StockEvaluation extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListStockItems::class;
    }

    protected function getStats(): array
    {
        $stockStats = app(\App\Services\Stats\StockStatService::class);
        $ids = $this->getPageTableQuery()->reorder()->select('id')->get()->pluck('id')->toArray();
        return [
            Stat::make(
                'تقييم المخزون (بسعر البيع)',
                fn() => number_format($stockStats->stockEvaluationBySellPrice($ids) ?? 0, 2) . ' جنيه'
            )
                ->description('القيمة الإجمالية للمخزون')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(
                'تقييم المخزون (بسعر الشراء)',
                fn() => number_format($stockStats->stockEvaluationByCostPrice($ids) ?? 0, 2) . ' جنيه'
            )
                ->description('القيمة الإجمالية للمخزون')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
