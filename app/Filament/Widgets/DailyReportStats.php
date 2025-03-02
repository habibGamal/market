<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\DailyReportResource;
use App\Services\Reports\DailyReportService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;
use Livewire\Attributes\On;

class DailyReportStats extends BaseWidget
{

    protected static ?string $pollingInterval = null;


    public $date;

    #[On('updateWidgets')]
    public function updateWidgets($date): void
    {
        $this->date = $date;
    }

    protected function getStats(): array
    {
        $date = $this->date ?? now()->format('Y-m-d');
        $service = app(DailyReportService::class);

        // Get all stats from service
        $workDay = $service->getWorkDay($date);
        $stockStats = $service->getStockEvaluation();
        $availableStock = $service->getAvailableStockEvaluation();
        $unavailableStock = $service->getUnavailableStockEvaluation();
        $reservedStock = $service->getReservedStockEvaluation();
        $orderStats = $service->getOrderStats($date);

        return [
            // Work Day Stats
            Stat::make('رصيد بداية اليوم', number_format($workDay->start_day ?? 0, 2) . ' جنيه')
                ->description('القيمة الإفتتاحية لليوم')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Blue),

            Stat::make('رصيد نهاية اليوم', number_format($workDay->total_day ?? 0, 2) . ' جنيه')
                ->description('القيمة النهائية لليوم')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Blue),

            // Stock Total Value
            Stat::make('تقييم المخزون الكلي (تكلفة)', number_format($stockStats['cost'], 2) . ' جنيه')
                ->description('القيمة الإجمالية للمخزون بسعر التكلفة')
                ->descriptionIcon('heroicon-m-calculator')
                ->color(Color::Blue),

            Stat::make('تقييم المخزون الكلي (بيع)', number_format($stockStats['price'], 2) . ' جنيه')
                ->description('القيمة الإجمالية للمخزون بسعر البيع')
                ->descriptionIcon('heroicon-m-calculator')
                ->color(Color::Blue),

            // Available Stock
            Stat::make('المخزون المتاح (تكلفة)', number_format($availableStock['cost'], 2) . ' جنيه')
                ->description('قيمة المخزون المتاح بسعر التكلفة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color(Color::Green),

            Stat::make('المخزون المتاح (بيع)', number_format($availableStock['price'], 2) . ' جنيه')
                ->description('قيمة المخزون المتاح بسعر البيع')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color(Color::Green),

            // Unavailable Stock
            Stat::make('المخزون غير المتاح (تكلفة)', number_format($unavailableStock['cost'], 2) . ' جنيه')
                ->description('قيمة المخزون غير المتاح بسعر التكلفة')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color(Color::Red),

            Stat::make('المخزون غير المتاح (بيع)', number_format($unavailableStock['price'], 2) . ' جنيه')
                ->description('قيمة المخزون غير المتاح بسعر البيع')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color(Color::Red),

            // Reserved Stock
            Stat::make('البضاعة المحجوزة (تكلفة)', number_format($reservedStock['cost'], 2) . ' جنيه')
                ->description('قيمة البضاعة المحجوزة بسعر التكلفة')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color(Color::Yellow),

            Stat::make('البضاعة المحجوزة (بيع)', number_format($reservedStock['price'], 2) . ' جنيه')
                ->description('قيمة البضاعة المحجوزة بسعر البيع')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color(Color::Yellow),

            // Order Stats
            Stat::make('عدد الطلبات', $orderStats['total_orders'])
                ->description('إجمالي عدد الطلبات')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color(Color::Blue),

            Stat::make('عدد المرتجعات', $orderStats['total_returns'])
                ->description('إجمالي عدد المرتجعات')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color(Color::Red),
        ];
    }
}
