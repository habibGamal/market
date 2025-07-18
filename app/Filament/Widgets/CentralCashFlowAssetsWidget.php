<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CentralCashFlowService;
use Filament\Support\Colors\Color;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CentralCashFlowAssetsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $cashFlowService = app(CentralCashFlowService::class);
        $assetsData = $cashFlowService->getCashFlowData($startDate, $endDate)['assets'];

        return [
            Stat::make('المصروفات المتتبعة', number_format($assetsData['tracked_expenses'], 2) . ' جنيه')
                ->description('مجموع المصروفات المفعلة للمتابعة خلال الفترة المحددة')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color(Color::Blue),

            Stat::make('رصيد الخزينة', number_format($assetsData['vault_balance'], 2) . ' جنيه')
                ->description('الرصيد الحالي في الخزينة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Green),

            Stat::make('تكلفة المخزون', number_format($assetsData['stock_cost'], 2) . ' جنيه')
                ->description('إجمالي تكلفة المنتجات المخزنة')
                ->descriptionIcon('heroicon-m-cube')
                ->color(Color::Amber),

            Stat::make('إجمالي الأصول', number_format($assetsData['total'], 2) . ' جنيه')
                ->description('مجموع جميع الأصول')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color(Color::Emerald),
        ];
    }
}
