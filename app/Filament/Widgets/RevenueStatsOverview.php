<?php

namespace App\Filament\Widgets;

use App\Services\Reports\RevenueReportService;
use Carbon\Carbon;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class RevenueStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public array $filterFormData = [];

    #[On('updateWidgets')]
    public function updateFilterFormData($filterFormData)
    {
        $this->filterFormData = $filterFormData;
    }

    protected function getStats(): array
    {
        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;

        $revenueReportService = app(RevenueReportService::class);
        $stats = $revenueReportService->getAllStats($startDate, $endDate);

        return [
            // Net Sales Revenue
            Stat::make('صافي المبيعات', number_format($stats['net_sales'], 2) . ' جنيه')
                ->description('إجمالي المبيعات بعد خصم المرتجعات')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color(Color::Blue),

            // Cost of Goods Sold
            Stat::make('تكلفة البضاعة المباعة', number_format($stats['cogs'], 2) . ' جنيه')
                ->description('إجمالي تكلفة المنتجات المباعة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color(Color::Gray),

            // Gross Profit
            Stat::make('مجمل الربح', number_format($stats['gross_profit'], 2) . ' جنيه')
                ->description(number_format($stats['gross_profit_margin'], 1) . '% هامش ربح إجمالي')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color(Color::Green),

            // Total Expenses
            Stat::make('اجمالي المصروفات', number_format($stats['total_expenses'], 2) . ' جنيه')
                ->description('إجمالي المصاريف التشغيلية')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color(Color::Red),

            // Net Profit
            Stat::make('صافي الربح', number_format($stats['net_profit'], 2) . ' جنيه')
                ->description(number_format($stats['net_profit_margin'], 1) . '% هامش صافي الربح')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(($stats['net_profit'] >= 0) ? Color::Emerald : Color::Red),
        ];
    }
}
