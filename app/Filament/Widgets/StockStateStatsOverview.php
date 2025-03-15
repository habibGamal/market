<?php

namespace App\Filament\Widgets;

use App\Services\Reports\StockStateReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockStateStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stockStateService = app(StockStateReportService::class);

        return [
            Stat::make('إجمالي تكلفة المخزون المتاح', number_format($stockStateService->getTotalAvailableStockCost(), 2) . ' EGP')
                ->description('القيمة الإجمالية للمخزون المتاح')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('إجمالي تكلفة المخزون الغير متاح بسبب المرتجعات', number_format($stockStateService->getTotalReturnedStockCost(), 2) . ' EGP')
                ->description('القيمة الإجمالية للمخزون المرتجع')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make('إجمالي تكلفة المخزون الغير متاح بسبب الهالك', number_format($stockStateService->getTotalWasteStockCost(), 2) . ' EGP')
                ->description('القيمة الإجمالية للمخزون الهالك')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
