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
        if (empty($this->tableFilters['report_filter'])) {
            $startDate = now()->startOfMonth();
            $endDate = now();
        } else {
            $startDate = Carbon::parse($this->tableFilters['report_filter']['start_date']);
            $endDate = Carbon::parse($this->tableFilters['report_filter']['end_date']);
        }

        $stats = app(OrderReportService::class)->getOrderStats($startDate, $endDate);
        $topCustomers = app(OrderReportService::class)->getCustomerStats($startDate, $endDate);

        $bestCustomer = !empty($topCustomers) ? $topCustomers[0] : null;

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
                ->color('warning'),

            $bestCustomer ?
            Stat::make('أفضل عميل', $bestCustomer->customer_name)
                ->description(sprintf('طلبات: %d | مبيعات: %s جنيه',
                    $bestCustomer->orders_count,
                    number_format($bestCustomer->total_sales, 2)
                ))
                ->descriptionIcon('heroicon-m-user')
                ->color('success') :
            Stat::make('أفضل عميل', 'لا يوجد بيانات')
                ->description('لا توجد طلبات في هذه الفترة')
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),
        ];
    }
}
