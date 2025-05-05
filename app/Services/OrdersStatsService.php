<?php

namespace App\Services;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Collection;

class OrdersStatsService
{
    public function calculateOrdersStats(Collection $orders): array
    {
        $total_orders = $orders->count();
        $total_sales = $orders->sum('total');
        $total_profit_without_discount = $orders->sum(function ($order) {
            return $order->items_sum_profit - $order->return_items_sum_profit;
        });
        $total_profit = $orders->sum(function ($order) {
            return $order->items_sum_profit - $order->return_items_sum_profit - $order->discount;
        });
        $total_returns = $orders->sum('return_items_sum_total');
        $average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
        $total_cancelled = $orders->load('cancelledItems')->where('status', OrderStatus::CANCELLED)->sum(
            function ($order) {
                return $order->cancelledItems->sum('total');
            }
        );

        return [
            'total_orders' => $total_orders,
            'total_sales' => $total_sales,
            'total_profit_without_discount' => $total_profit_without_discount,
            'total_profit' => $total_profit,
            'total_returns' => $total_returns,
            'average_order_value' => $average_order_value,
            'total_cancelled' => $total_cancelled,
        ];
    }

    public function getOrdersWithStats($query)
    {
        return $query->withSum('items', 'profit')
            ->withSum('returnItems', 'profit')
            ->withSum('returnItems', 'total')
            ->get();
    }
}
