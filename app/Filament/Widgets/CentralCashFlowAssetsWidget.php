<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CentralCashFlowService;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CentralCashFlowAssetsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $cashFlowService = app(CentralCashFlowService::class);
        $cashFlowData = $cashFlowService->getCashFlowData();
        $assetsData = $cashFlowData['assets'];
        $trackedExpenses = $cashFlowData['tracked_expenses'];

        $stats = [
            Stat::make('تكلفة المخزون', number_format($assetsData['stock_cost'], 2) . ' جنيه')
                ->description('إجمالي تكلفة المنتجات في المخزون')
                ->descriptionIcon('heroicon-m-cube')
                ->color(Color::Blue),

            Stat::make('تكلفة الطلبات قيد التسليم', number_format($assetsData['delivery_orders_cost'], 2) . ' جنيه')
                ->description('إجمالي تكلفة الطلبات في طريقها للتسليم')
                ->descriptionIcon('heroicon-m-truck')
                ->color(Color::Orange),

            Stat::make('رصيد الخزينة النقدية', number_format($assetsData['vault_balance'], 2) . ' جنيه')
                ->description('المبلغ المتوفر في الخزينة النقدية الافتراضية')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Green),

            Stat::make('أرصدة الخزائن الأخرى', number_format($assetsData['other_vaults_balance'], 2) . ' جنيه')
                ->description('مجموع أرصدة الخزائن الأخرى')
                ->descriptionIcon('heroicon-m-building-library')
                ->color(Color::Teal),

            Stat::make('أرصدة مندوبين التسليم', number_format($assetsData['drivers_balance'], 2) . ' جنيه')
                ->description('إجمالي أرصدة جميع مندوبين التسليم')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Cyan),

            Stat::make('ايرادات مستحقة غير مدفوعة', number_format($assetsData['cash_settlements_in_unpaid'], 2) . ' جنيه')
                ->description('إجمالي اليرادات المستحقة غير المدفوعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Yellow),

            Stat::make('الأصول الثابتة', number_format($assetsData['fixed_assets'], 2) . ' جنيه')
                ->description('إجمالي قيمة الأصول الثابتة')
                ->descriptionIcon('heroicon-m-building-office')
                ->color(Color::Purple),
        ];

        // Add tracked expense types
        foreach ($trackedExpenses as $expense) {
            $stats[] = Stat::make($expense['name'], number_format($expense['total'], 2) . ' جنيه')
                ->description('إجمالي مصروفات ' . $expense['name'])
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color(Color::Red);
        }

        // Add total assets at the end
        $stats[] = Stat::make('إجمالي الأصول', number_format($assetsData['total'], 2) . ' جنيه')
            ->description('مجموع جميع الأصول')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color(Color::Emerald);

        return $stats;
    }
}
