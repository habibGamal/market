<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\CartItemsByProductsReportResource\Pages\ListCartItemsByProductsReports;
use App\Models\CartItem;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CartTotalsByProductsStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCartItemsByProductsReports::class;
    }

    protected function getStats(): array
    {
        $stats = CartItem::whereIn('product_id', $this->getPageTableQuery()->select('id')->get()->pluck('id'))
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->select(
                DB::raw('SUM(packets_quantity) as total_packets'),
                DB::raw('SUM(piece_quantity) as total_pieces'),
                DB::raw('SUM(packets_quantity * products.packet_price + piece_quantity * products.piece_price) as total_value')
            )
            ->first();

        return [
            Stat::make('إجمالي العبوات', number_format($stats->total_packets))
                ->description('إجمالي عدد العبوات في السلات')
                ->icon('heroicon-o-cube')
                ->color('success'),

            Stat::make('إجمالي القطع', number_format($stats->total_pieces))
                ->description('إجمالي عدد القطع في السلات')
                ->icon('heroicon-o-squares-2x2')
                ->color('primary'),

            Stat::make('إجمالي القيمة', number_format($stats->total_value, 2) . ' EGP')
                ->description('القيمة الإجمالية للمنتجات في السلات')
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),
        ];
    }
}
