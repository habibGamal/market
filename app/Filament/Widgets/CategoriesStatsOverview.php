<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\CategoriesReportResource\Pages\ListCategoriesReports;
use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CategoriesStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return ListCategoriesReports::class;
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

        $mostOrderedCategory = $this->getMostOrderedCategory($startDate, $endDate);
        $mostProfitableCategory = $this->getMostProfitableCategory($startDate, $endDate);
        $leastOrderedCategory = $this->getLeastOrderedCategory($startDate, $endDate);
        $leastProfitableCategory = $this->getLeastProfitableCategory($startDate, $endDate);
        $mostTrendingCategory = $this->getMostTrendingCategory($startDate, $endDate);
        $mostReturnedCategory = $this->getMostReturnedCategory($startDate, $endDate);

        return [
            $this->formatCategoryStat(
                $mostOrderedCategory,
                'أكثر الفئات طلبًا',
                'heroicon-o-shopping-bag',
                'primary'
            ),

            $this->formatCategoryStat(
                $mostProfitableCategory,
                'أكثر الفئات ربحية',
                'heroicon-o-banknotes',
                'success'
            ),

            $this->formatCategoryStat(
                $mostTrendingCategory,
                'أكثر الفئات رواجًا',
                'heroicon-o-arrow-trending-up',
                'success'
            ),

            $this->formatCategoryStat(
                $leastOrderedCategory,
                'أقل الفئات طلبًا',
                'heroicon-o-shopping-bag',
                'warning'
            ),

            $this->formatCategoryStat(
                $leastProfitableCategory,
                'أقل الفئات ربحية',
                'heroicon-o-banknotes',
                'danger'
            ),

            $this->formatCategoryStat(
                $mostReturnedCategory,
                'أكثر الفئات مرتجعات',
                'heroicon-o-arrow-uturn-left',
                'danger'
            ),
        ];
    }

    private function formatCategoryStat($data, string $title, string $icon, string $color): Stat
    {
        if (empty($data)) {
            return Stat::make($title, 'لا توجد بيانات')
                ->icon($icon)
                ->color($color);
        }

        $trendData = $this->getCategoryTrend($data->category_id ?? null);

        return Stat::make($title, $data->category_name)
            ->description(
                view('filament.pages.reports.badges', [
                    'data' => $data
                ])
            )
            ->icon($icon)
            ->chart($trendData)
            ->color($color);
    }

    private function getCategoryTrend(?int $categoryId): array
    {
        if (!$categoryId) {
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
                ->where('products.category_id', $categoryId)
        )
            ->dateColumn('orders.created_at')
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity');

        return $trend->map(fn(TrendValue $value) => $value->aggregate)->toArray();
    }

    private function getMostOrderedCategory(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->first();
    }

    private function getMostProfitableCategory(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_profit')
            ->first();
    }

    private function getLeastOrderedCategory(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_quantity')
            ->first();
    }

    private function getLeastProfitableCategory(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_profit')
            ->first();
    }

    private function getMostTrendingCategory(Carbon $startDate, Carbon $endDate)
    {
        $trendPeriod = intval($endDate->diffInDays($startDate) * -0.3);
        $trendStartDate = $endDate->copy()->subDays($trendPeriod);

        return OrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$trendStartDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc(
                DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) / DATEDIFF("' . $endDate->format('Y-m-d') . '", "' . $trendStartDate->format('Y-m-d') . '")')
            )
            ->first();
    }

    private function getMostReturnedCategory(Carbon $startDate, Carbon $endDate)
    {
        return ReturnOrderItem::select(
            'categories.name as category_name',
            'categories.id as category_id',
            DB::raw('SUM(return_order_items.packets_quantity * products.packet_to_piece + return_order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(return_order_items.total) as total_value')
        )
            ->join('products', 'return_order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('return_order_items.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->first();
    }
}
