<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\CartItemsByCustomersReportResource\Pages\ListCartItemsByCustomersReports;
use App\Models\Cart;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CartTotalsByCustomersStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCartItemsByCustomersReports::class;
    }

    protected function getStats(): array
    {

        $totalCartsValue = Cart::whereIn('customer_id', $this->getPageTableQuery()->select('id')->get()->pluck('id'))->sum('total');
        $activeCartsCount = Cart::whereIn('customer_id', $this->getPageTableQuery()->select('id')->get()->pluck('id'))->where('total', '>', 0)->count();

        return [
            Stat::make('إجمالي قيم السلات', number_format($totalCartsValue, 2) . ' EGP')
                ->description('القيمة الإجمالية لجميع السلات')
                ->icon('heroicon-o-shopping-cart')
                ->color('success'),

            Stat::make('عدد السلات النشطة', $activeCartsCount)
                ->description('عدد السلات التي تحتوي على منتجات')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),
        ];
    }
}
