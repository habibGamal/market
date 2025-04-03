<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Reports\ProductsReportResource\Pages\ListProductsReports;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProductsStatsOverview extends BaseWidget
{

    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    // protected int|string|array $columnSpan = 'full';

    public function getColumns(): int
    {
        return 3;
    }

    public array $filterFormData = [];


    protected function getTablePage(): string
    {
        return ListProductsReports::class;
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


        $mostOrderedProduct = $this->getMostOrderedProduct($startDate, $endDate);


        $mostProfitableProduct = $this->getMostProfitableProduct($startDate, $endDate);


        $leastOrderedProduct = $this->getLeastOrderedProduct($startDate, $endDate);


        $leastProfitableProduct = $this->getLeastProfitableProduct($startDate, $endDate);


        $mostTrendingProduct = $this->getMostTrendingProduct($startDate, $endDate);


        $mostReturnedProduct = $this->getMostReturnedProduct($startDate, $endDate);

        // Get sales products percentage
        $productSalesPercentage = $this->getProductSalesPercentage($startDate, $endDate);

        return [

            $this->formatProductStat(
                $mostOrderedProduct,
                'أكثر المنتجات طلبًا',
                'heroicon-o-shopping-bag',
                'primary'
            ),


            $this->formatProductStat(
                $mostProfitableProduct,
                'أكثر المنتجات ربحية',
                'heroicon-o-banknotes',
                'success'
            ),

            $this->formatProductStat(
                $mostTrendingProduct,
                'أكثر المنتجات رواجًا',
                'heroicon-o-arrow-trending-up',
                'success'
            ),

            $this->formatProductStat(
                $leastOrderedProduct,
                'أقل المنتجات طلبًا',
                'heroicon-o-shopping-bag',
                'warning'
            ),


            $this->formatProductStat(

                $leastProfitableProduct,
                'أقل المنتجات ربحية',
                'heroicon-o-banknotes',
                'danger'
            ),

            $this->formatProductStat(
                $mostReturnedProduct,
                'أكثر المنتجات مرتجعات',
                'heroicon-o-arrow-uturn-left',
                'danger'
            ),

            // Add the new sales percentage stat
            Stat::make('نسبة المنتجات المباعة', $productSalesPercentage ? number_format($productSalesPercentage['sales_percentage'], 2) . '%' : 'لا توجد بيانات')
                ->description(
                    $productSalesPercentage ? 'عدد المنتجات المباعة: ' . $productSalesPercentage['products_with_sales'] . ' من أصل ' . $productSalesPercentage['total_products'] : ''
                )
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }

    private function formatProductStat($data, string $title, string $icon, string $color): Stat
    {
        if (empty($data)) {
            return Stat::make($title, 'لا توجد بيانات')
                ->icon($icon)
                ->color($color);
        }

        $trendData = $this->getProductTrend($data->product_id ?? null);
        if(!auth()->user()->can('view_profits_product')){
            $data->total_profit = null;
        }
        return Stat::make($title, $data->product_name)
            ->description(
                view('filament.pages.reports.badges', [
                    'data' => $data
                ])

            )
            ->icon($icon)
            ->chart($trendData)
            ->color($color);
    }

    private function getProductTrend(?int $productId): array
    {
        if (!$productId) {
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
                ->where('product_id', $productId)
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
        )
            ->dateColumn('orders.created_at')
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity')
        ;
        return $trend->map(fn(TrendValue $value) => $value->aggregate)->toArray();
    }

    /**
     * Calculate the percentage of products with sales versus products without sales
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array|null
     */
    private function getProductSalesPercentage(Carbon $startDate, Carbon $endDate): ?array
    {
        $totalProducts = Product::count();

        if ($totalProducts === 0) {
            return null;
        }

        $productsWithSales = Product::whereHas('orderItems', function ($query) use ($startDate, $endDate) {
            $query->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate]);
        })->count();

        $salesPercentage = ($productsWithSales / $totalProducts) * 100;

        return [
            'total_products' => $totalProducts,
            'products_with_sales' => $productsWithSales,
            'products_without_sales' => $totalProducts - $productsWithSales,
            'sales_percentage' => $salesPercentage,
        ];
    }

    private function getMostOrderedProduct(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.id')
            ->orderByDesc('total_quantity')
            ->first();
    }

    private function getMostProfitableProduct(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.id')
            ->orderByDesc('total_profit')
            ->first();
    }

    private function getLeastOrderedProduct(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.id')
            ->orderBy('total_quantity')
            ->first();
    }

    private function getLeastProfitableProduct(Carbon $startDate, Carbon $endDate)
    {
        return OrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.id')
            ->orderBy('total_profit')
            ->first();
    }

    private function getMostTrendingProduct(Carbon $startDate, Carbon $endDate)
    {

        $trendPeriod = intval($endDate->diffInDays($startDate) * -0.3);
        $trendStartDate = $endDate->copy()->subDays($trendPeriod);

        return OrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(order_items.total) as total_value'),
            DB::raw('SUM(order_items.profit) as total_profit')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$trendStartDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.id')
            ->orderByDesc(
                DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) / DATEDIFF("' . $endDate->format('Y-m-d') . '", "' . $trendStartDate->format('Y-m-d') . '")')
            )
            ->first();
    }

    private function getMostReturnedProduct(Carbon $startDate, Carbon $endDate)
    {
        return ReturnOrderItem::select(
            'products.name as product_name',
            'products.id as product_id',
            DB::raw('SUM(return_order_items.packets_quantity * products.packet_to_piece + return_order_items.piece_quantity) as total_quantity'),
            DB::raw('SUM(return_order_items.total) as total_value')
        )
            ->join('products', 'return_order_items.product_id', '=', 'products.id')
            ->whereBetween('return_order_items.created_at', [$startDate, $endDate])
            ->groupBy('return_order_items.product_id', 'products.name', 'products.id')
            ->orderByDesc('total_quantity')
            ->first();
    }
}
