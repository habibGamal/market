<?php

namespace App\Filament\Widgets;

use App\Services\Reports\InvoiceReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class ReturnPurchaseInvoiceStats extends BaseWidget
{

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;

        $stats = app(InvoiceReportService::class)->getReturnStats($startDate, $endDate);

        return [
            Stat::make('إجمالي المرتجعات', number_format($stats['total_returns'], 2) . ' جنية')
                ->description('القيمة الإجمالية لفواتير مرتجعات المشتريات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('info'),

            Stat::make('المصروف', number_format($stats['total_issued'], 2) . ' جنية')
                ->description('إجمالي المرتجعات المصروفة بأذونات صرف')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('غير المصروف', number_format($stats['total_not_issued'], 2) . ' جنية')
                ->description('إجمالي المرتجعات التي لا تزال بدون أذونات صرف')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    public array $filterFormData = [];

    #[On('updateWidgets')]
    public function updateWidgets(array $filterFormData): void
    {
        $this->filterFormData = $filterFormData;
    }
}
