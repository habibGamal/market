<?php

namespace App\Filament\Widgets;

use App\Services\Reports\InvoiceReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class WasteInvoiceStats extends BaseWidget
{

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;

        $stats = app(InvoiceReportService::class)->getWasteStats($startDate, $endDate);

        return [
            Stat::make('إجمالي الهالك', number_format($stats['total_waste'], 2) . ' جنية')
                ->description('القيمة الإجمالية لفواتير الهالك')
                ->descriptionIcon('heroicon-m-trash')
                ->color('info'),

            Stat::make('المصروف', number_format($stats['total_issued'], 2) . ' جنية')
                ->description('إجمالي الهالك المصروف بأذونات صرف')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('غير المصروف', number_format($stats['total_not_issued'], 2) . ' جنية')
                ->description('إجمالي الهالك الذي لا يزال بدون أذونات صرف')
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
