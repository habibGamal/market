<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CentralCashFlowService;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CentralCashFlowResponsibilitiesWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $cashFlowService = app(CentralCashFlowService::class);
        $data = $cashFlowService->getCashFlowData();
        $responsibilitiesData = $data['responsibilities'];
        $assetEntries = $data['asset_entries'];
        $finalResult = $data['final_result'];
        $isProfit = $data['is_profit'];

        return [
            Stat::make('أرصدة الموردين', number_format($responsibilitiesData['suppliers_balance'], 2) . ' جنيه')
                ->description('إجمالي المبالغ المستحقة للموردين')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Red),

            // Stat::make('التسويات الخارجة (مدفوعة)', number_format($responsibilitiesData['cash_settlements_out_paid'], 2) . ' جنيه')
            //     ->description('إجمالي التسويات النقدية الخارجة المدفوعة')
            //     ->descriptionIcon('heroicon-m-arrow-up-circle')
            //     ->color(Color::Rose),

            Stat::make('التزامات قصيرة الاجل', number_format($responsibilitiesData['cash_settlements_out_unpaid'], 2) . ' جنيه')
                ->description('إجمالي التزامات قصيرة الاجل غير المدفوعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Amber),

            Stat::make('إجمالي الالتزامات', number_format($responsibilitiesData['total'], 2) . ' جنيه')
                ->description('مجموع جميع الالتزامات')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color(Color::Rose),

            Stat::make('إدخالات رأس المال', number_format($assetEntries, 2) . ' جنيه')
                ->description('المبالغ المستثمرة في الأصول')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color(Color::Orange),

            Stat::make(
                $isProfit ? 'الربح النهائي' : 'الخسارة النهائية',
                number_format(abs($finalResult), 2) . ' جنيه'
            )
                ->description($isProfit ? 'إجمالي الربح المحقق' : 'إجمالي الخسارة المتكبدة')
                ->descriptionIcon($isProfit ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($isProfit ? Color::Emerald : Color::Red),
        ];
    }
}
