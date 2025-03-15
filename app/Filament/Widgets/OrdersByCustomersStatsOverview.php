<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\OrdersByCustomersReportResource\Pages\ListOrdersByCustomersReports;
use App\Services\Reports\OrdersByCustomersReportService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class OrdersByCustomersStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrdersByCustomersReports::class;
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

        $stats = app(OrdersByCustomersReportService::class)->getBestCustomerStats($startDate, $endDate, $this->getPageTableQuery());
        $bestCustomer = $stats['best_customer'];

        return [
            Stat::make('أفضل عميل', $bestCustomer ? $bestCustomer['name'] : 'لا يوجد')
                ->description($bestCustomer ? sprintf('عدد الطلبات: %d | المبيعات: %s جنيه',
                    $bestCustomer['orders_count'],
                    number_format($bestCustomer['total_sales'], 2)
                ) : 'لا توجد طلبات في هذه الفترة')
                ->descriptionIcon('heroicon-m-star')
                ->color($bestCustomer ? 'success' : 'gray'),

            Stat::make('إجمالي العملاء النشطين', number_format($stats['total_customers']))
                ->description('العملاء الذين لديهم طلبات في هذه الفترة')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
