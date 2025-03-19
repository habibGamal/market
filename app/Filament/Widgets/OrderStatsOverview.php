<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\OrdersReportResource\Pages\ListOrdersReports;
use App\Services\OrdersStatsService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrdersReports::class;
    }

    protected function getStats(): array
    {
        $statsService = app(OrdersStatsService::class);
        $result = $statsService->getOrdersWithStats($this->getPageTableQuery());
        $stats = $statsService->calculateOrderStats($result);

        return [
            Stat::make('إجمالي الطلبات', number_format($stats['total_orders']))
                ->description('إجمالي عدد الطلبات')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('إجمالي المبيعات', number_format($stats['total_sales'], 2) . ' جنيه')
                ->description('القيمة الإجمالية للمبيعات')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('إجمالي الأرباح', number_format($stats['total_profit'], 2) . ' جنيه')
                ->description('الأرباح الإجمالية')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('متوسط قيمة الطلب', number_format($stats['average_order_value'], 2) . ' جنيه')
                ->description('متوسط قيمة الطلب الواحد')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('إجمالي المرتجعات', number_format($stats['total_returns'], 2) . ' جنيه')
                ->description('القيمة الإجمالية للمرتجعات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),

            Stat::make('إجمالي الملغية', number_format($stats['total_cancelled'], 2) . ' جنيه')
                ->description('القيمة الإجمالية للطلبات الملغية')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning')
        ];
    }
}
