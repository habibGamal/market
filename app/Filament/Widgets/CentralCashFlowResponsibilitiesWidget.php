<?php

namespace App\Filament\Widgets;

use App\Services\Reports\CentralCashFlowService;
use Filament\Support\Colors\Color;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CentralCashFlowResponsibilitiesWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $cashFlowService = app(CentralCashFlowService::class);
        $data = $cashFlowService->getCashFlowData($startDate, $endDate);
        $responsibilitiesData = $data['responsibilities'];

        return [
            Stat::make('أرصدة الموردين', number_format($responsibilitiesData['suppliers_balance'], 2) . ' جنيه')
                ->description('إجمالي المبالغ المستحقة للموردين')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Red),

            Stat::make('إدخالات الأصول', number_format($responsibilitiesData['asset_entries'], 2) . ' جنيه')
                ->description('مجموع إدخالات الأصول خلال الفترة')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color(Color::Orange),

            Stat::make('إجمالي الالتزامات', number_format($responsibilitiesData['total'], 2) . ' جنيه')
                ->description('مجموع جميع الالتزامات')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color(Color::Rose),


            Stat::make('صافي المركز المالي', number_format($data['net_position'], 2) . ' جنيه')
                ->description('الفرق بين الأصول والالتزامات')
                ->descriptionIcon('heroicon-m-scale')
                ->color($data['net_position'] >= 0 ? Color::Emerald : Color::Red),
        ];
    }
}
