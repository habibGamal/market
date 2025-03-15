<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\OrdersByAreasReportResource\Pages\ListOrdersByAreasReports;
use App\Services\Reports\OrdersByAreasReportService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class OrdersByAreasStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrdersByAreasReports::class;
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

        $stats = app(OrdersByAreasReportService::class)->getBestAreaStats($startDate, $endDate, $this->getPageTableQuery());
        $bestArea = $stats['best_area'];

        return [
            Stat::make('أفضل منطقة', $bestArea ? $bestArea['name'] : 'لا يوجد')
                ->description($bestArea ? sprintf('عدد الطلبات: %d | المبيعات: %s جنيه',
                    $bestArea['orders_count'],
                    number_format($bestArea['total_sales'], 2)
                ) : 'لا توجد طلبات في هذه الفترة')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color($bestArea ? 'success' : 'gray'),

            Stat::make('إجمالي المناطق النشطة', number_format($stats['total_areas']))
                ->description('المناطق التي لديها طلبات في هذه الفترة')
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),
        ];
    }
}
