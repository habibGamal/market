<?php

namespace App\Services\Reports;

use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductReportService
{
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $subQuery = $this->getOrderItemsSubQuery($data);
        $returnSubQuery = $this->getReturnOrderItemsSubQuery($data);

        return $this->buildMainQuery($query, $subQuery, $returnSubQuery);
    }

    private function getOrderItemsSubQuery(array $data)
    {
        return DB::table('order_items')
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.piece_quantity) as order_items_sum_piece_quantity'),
                DB::raw('SUM(order_items.packets_quantity) as order_items_sum_packets_quantity'),
                DB::raw('SUM(order_items.total) as order_items_sum_total'),
                DB::raw('SUM(order_items.profit) as order_items_sum_profit')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('order_items.product_id');
    }

    private function getReturnOrderItemsSubQuery(array $data)
    {
        return DB::table('return_order_items')
            ->select(
                'return_order_items.product_id',
                DB::raw('SUM(return_order_items.piece_quantity) as return_order_items_sum_piece_quantity'),
                DB::raw('SUM(return_order_items.packets_quantity) as return_order_items_sum_packets_quantity')
            )
            ->whereBetween('return_order_items.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('return_order_items.product_id');
    }

    private function buildMainQuery(Builder $query, $subQuery, $returnSubQuery): Builder
    {
        return $query->addSelect([
            'products.*',
            DB::raw('agg.order_items_sum_packets_quantity * products.packet_to_piece + agg.order_items_sum_piece_quantity as order_items_sum_piece_quantity'),
            DB::raw('COALESCE(return_agg.return_order_items_sum_packets_quantity * products.packet_to_piece + return_agg.return_order_items_sum_piece_quantity, 0) as return_order_items_sum_piece_quantity'),
            'order_items_sum_total' => 'agg.order_items_sum_total',
            'order_items_sum_profit' => 'agg.order_items_sum_profit',
        ])
            ->leftJoinSub($subQuery, 'agg', function ($join) {
                $join->on('products.id', '=', 'agg.product_id');
            })
            ->leftJoinSub($returnSubQuery, 'return_agg', function ($join) {
                $join->on('products.id', '=', 'return_agg.product_id');
            });
    }

    public function getProductSalesChartData($product, $start = null, $end = null, int $days = 30): array
    {
        $start = $start ? Carbon::parse($start) : now()->subDays($days);
        $end = $end ? Carbon::parse($end) : now();

        $query = OrderItem::query()
            ->where('product_id', $product->id)
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id');

        $trendQuantity = Trend::query($query->clone())
            ->dateColumn('orders.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity');

        $trendTotal = Trend::query($query->clone())
            ->dateColumn('orders.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('order_items.total');

        $trendProfit = Trend::query($query->clone())
            ->dateColumn('orders.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('order_items.profit');

        return [
            'quantities' => $trendQuantity->map(fn(TrendValue $value) => $value->aggregate),
            'values' => $trendTotal->map(fn(TrendValue $value) => $value->aggregate),
            'profits' => $trendProfit->map(fn(TrendValue $value) => $value->aggregate),
            'labels' => $trendQuantity->map(fn(TrendValue $value) => $value->date),
        ];
    }

    public function getProductReturnsChartData($product, $start = null, $end = null, int $days = 30): array
    {
        $start = $start ? Carbon::parse($start) : now()->subDays($days);
        $end = $end ? Carbon::parse($end) : now();

        $query = ReturnOrderItem::query()
            ->where('product_id', $product->id)
            ->join('products', 'return_order_items.product_id', '=', 'products.id');

        $trendQuantity = Trend::query($query->clone())
            ->dateColumn('return_order_items.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('return_order_items.packets_quantity * products.packet_to_piece + return_order_items.piece_quantity');

        $trendTotal = Trend::query($query->clone())
            ->dateColumn('return_order_items.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('return_order_items.total');

        return [
            'quantities' => $trendQuantity->map(fn(TrendValue $value) => $value->aggregate),
            'values' => $trendTotal->map(fn(TrendValue $value) => $value->aggregate),
            'labels' => $trendQuantity->map(fn(TrendValue $value) => $value->date),
        ];
    }
}
