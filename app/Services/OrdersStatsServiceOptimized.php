<?php

namespace App\Services;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrdersStatsServiceOptimized
{
    public function calculateOrdersStats(Builder $query): array
    {
        // Clone the query and remove ordering/limits to perform aggregation
        $statsQuery = clone $query;
        $statsQuery->getQuery()->orders = null;
        $statsQuery->getQuery()->limit = null;
        $statsQuery->getQuery()->offset = null;

        $stats = $statsQuery->select([
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('COALESCE(SUM(orders.total), 0) as total_sales'),
            DB::raw('COALESCE(SUM(orders.discount), 0) as total_discounts'),
            DB::raw('COALESCE(SUM(
                (SELECT COALESCE(SUM(profit), 0) FROM order_items WHERE order_items.order_id = orders.id)
            ), 0) as total_items_profit'),
            DB::raw('COALESCE(SUM(
                (SELECT COALESCE(SUM(profit), 0) FROM return_order_items WHERE return_order_items.order_id = orders.id)
            ), 0) as total_returns_profit'),
            DB::raw('COALESCE(SUM(
                (SELECT COALESCE(SUM(total), 0) FROM return_order_items WHERE return_order_items.order_id = orders.id)
            ), 0) as total_returns'),
            DB::raw('COALESCE(SUM(
                CASE
                    WHEN orders.status = "' . OrderStatus::CANCELLED->value . '"
                    THEN (SELECT COALESCE(SUM(total), 0) FROM cancelled_order_items WHERE cancelled_order_items.order_id = orders.id)
                    ELSE 0
                END
            ), 0) as total_cancelled'),
        ])->first();

        $total_orders = $stats->total_orders ?? 0;
        $total_sales = $stats->total_sales ?? 0;
        $total_items_profit = $stats->total_items_profit ?? 0;
        $total_returns_profit = $stats->total_returns_profit ?? 0;
        $total_discounts = $stats->total_discounts ?? 0;

        $total_profit_without_discount = $total_items_profit - $total_returns_profit;
        $total_profit = $total_profit_without_discount - $total_discounts;
        $average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;

        return [
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'total_profit_without_discount' => $total_profit_without_discount,
            'total_profit' => $total_profit,
            'total_returns' => $stats->total_returns ?? 0,
            'average_order_value' => $average_order_value,
            'total_cancelled' => $stats->total_cancelled ?? 0,
        ];
    }
}
