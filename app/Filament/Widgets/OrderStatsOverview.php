<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\OrdersReportResource\Pages\ListOrdersReports;
use App\Services\Reports\OrderReportService;
use Carbon\Carbon;
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
        $result = $this->getPageTableQuery()
        ->withSum('items', 'profit')
        ->withSum('returnItems', 'profit')
        ->withSum('returnItems', 'total')
        ->get();

        $total_orders = $result->count();
        $total_sales = $result->sum('total');
        $total_profit = $result->sum(function ($order) {
            return $order->items_sum_profit - $order->return_items_sum_profit;
        });
        $total_returns = $result->sum('return_items_sum_total');
        $average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
        $total_cancelled = $result->where('status', '=', 'cancelled')->sum('total');

        return [
            Stat::make('إجمالي الطلبات', number_format($total_orders))
                ->description('إجمالي عدد الطلبات')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('إجمالي المبيعات', number_format($total_sales, 2) . ' جنيه')
                ->description('القيمة الإجمالية للمبيعات')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('إجمالي الأرباح', number_format($total_profit, 2) . ' جنيه')
                ->description('الأرباح الإجمالية')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('متوسط قيمة الطلب', number_format($average_order_value, 2) . ' جنيه')
                ->description('متوسط قيمة الطلب الواحد')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('إجمالي المرتجعات', number_format($total_returns, 2) . ' جنيه')
                ->description('القيمة الإجمالية للمرتجعات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),

            Stat::make('إجمالي الملغية', number_format($total_cancelled, 2) . ' جنيه')
                ->description('القيمة الإجمالية للطلبات الملغية')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning')
        ];
    }
}
