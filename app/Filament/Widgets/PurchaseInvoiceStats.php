<?php

namespace App\Filament\Widgets;

use App\Services\Reports\InvoiceReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class PurchaseInvoiceStats extends BaseWidget
{

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filterFormData['start_date'] ?? null;
        $endDate = $this->filterFormData['end_date'] ?? null;

        $stats = app(InvoiceReportService::class)->getPurchaseStats($startDate, $endDate);

        return [
            Stat::make('إجمالي المشتريات', number_format($stats['total_purchases'], 2) . ' جنية')
                ->description('القيمة الإجمالية لفواتير المشتريات')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('المستلم', number_format($stats['total_received'], 2) . ' جنية')
                ->description('إجمالي المشتريات المستلمة بأذونات استلام')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('غير المستلم', number_format($stats['total_not_received'], 2) . ' جنية')
                ->description('إجمالي المشتريات التي لا تزال بدون أذونات استلام')
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
