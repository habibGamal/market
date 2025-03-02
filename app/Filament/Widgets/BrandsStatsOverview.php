<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\BrandsReportResource\Pages\ListBrandsReports;
use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BrandsStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListBrandsReports::class;
    }

    protected function getStats(): array
    {
        if (empty($this->tableFilters['report_filter'])) {
            $startDate = now()->startOfMonth()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = Carbon::parse($this->tableFilters['report_filter']['start_date']);
            $endDate = Carbon::parse($this->tableFilters['report_filter']['end_date']);
        }

        $mostOrderedBrand = $this->getMostOrderedBrand($startDate, $endDate);
        $mostProfitableBrand = $this->getMostProfitableBrand($startDate, $endDate);
        $leastOrderedBrand = $this->getLeastOrderedBrand($startDate, $endDate);
        $leastProfitableBrand = $this->getLeastProfitableBrand($startDate, $endDate);
        $mostTrendingBrand = $this->getMostTrendingBrand($startDate, $endDate);
        $mostReturnedBrand = $this->getMostReturnedBrand($startDate, $endDate);

        return [
            $this->formatBrandStat(
                $mostOrderedBrand,
                'أكثر العلامات التجارية طلبًا',
                'heroicon-o-shopping-bag',
                'primary'
            ),

            $this->formatBrandStat(
                $mostProfitableBrand,
                'أكثر العلامات التجارية ربحية',
                'heroicon-o-banknotes',
                'success'
            ),

            $this->formatBrandStat(
                $mostTrendingBrand,
                'أكثر العلامات التجارية رواجًا',
                'heroicon-o-arrow-trending-up',
                'success'
            ),

            $this->formatBrandStat(
                $leastOrderedBrand,
                'أقل العلامات التجارية طلبًا',
                'heroicon-o-shopping-bag',
                'warning'
            ),

            $this->formatBrandStat(
                $leastProfitableBrand,
                'أقل العلامات التجارية ربحية',
                'heroicon-o-banknotes',
                'danger'
            ),

            $this->formatBrandStat(
                $mostReturnedBrand,
                'أكثر العلامات التجارية مرتجعات',
                'heroicon-o-arrow-uturn-left',
                'danger'
            ),
        ];
    }

    private function formatBrandStat($data, string $title, string $icon, string $color): Stat
    {
        if (empty($data)) {
            return Stat::make($title, 'لا توجد بيانات')
                ->icon($icon)
                ->color($color);
        }

        $trendData = $this->getBrandTrend($data->brand_id ?? null);

        return Stat::make($title, $data->brand_name)
            ->description(
                view('filament.pages.reports.badges', [
                    'data' => $data
                ])
            )
            ->icon($icon)
            ->chart($trendData)
            ->color($color);
    }

    private function getBrandTrend(?int $brandId): array
    {
        if (!$brandId) {
            return [0, 0, 0, 0, 0, 0, 0];
        }

        if (empty($this->filters)) {
            $startDate = now()->startOfMonth()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = Carbon::parse($this->filters['start_date']);
            $endDate = Carbon::parse($this->filters['end_date']);
        }

        $trend = Trend::query(
            OrderItem::query()
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.brand_id', $brandId)
        )
            ->dateColumn('orders.created_at')
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity');

        return $trend->map(fn(TrendValue $value) => $value->aggregate)->toArray();
    }

    private function getMostOrderedBrand(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_quantity')
            ->first();
    }

    private function getMostProfitableBrand(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_profit')
            ->first();
    }

    private function getLeastOrderedBrand(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('total_quantity')
            ->first();
    }

    private function getLeastProfitableBrand(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('total_profit')
            ->first();
    }

    private function getMostTrendingBrand(Carbon $startDate, Carbon $endDate)
    {
        $trendPeriod = intval($endDate->diffInDays($startDate) * -0.3);
        $trendStartDate = $endDate->copy()->subDays($trendPeriod);

        return OrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$trendStartDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc(
                DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) / DATEDIFF("' . $endDate->format('Y-m-d') . '", "' . $trendStartDate->format('Y-m-d') . '")')
            )
            ->first();
    }

    private function getMostReturnedBrand(Carbon $startDate, Carbon $endDate)
    {
        return ReturnOrderItem::select(
            'brands.name as brand_name',
            'brands.id as brand_id',
            DB::raw('SUM(return_order_items.packets_quantity * products.packet_to_piece + return_order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(return_order_items.total) as total_value')
        )
            ->join('products', 'return_order_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('return_order_items.created_at', [$startDate, $endDate])
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_quantity')
            ->first();
    }
}
