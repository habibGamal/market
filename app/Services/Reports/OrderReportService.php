<?php

namespace App\Services\Reports;

use App\Models\CancelledOrderItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderReportService
{

    public function getOrdersChartData($start = null, $end = null, int $days = 30): array
    {
        $start = $start ? Carbon::parse($start) : now()->subDays($days);
        $end = $end ? Carbon::parse($end) : now();

        $query = Order::query();

        $ordersCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();

        $ordersSales = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        $ordersReturns = Trend::model(ReturnOrderItem::class)
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        $ordersCancelled = Trend::model(CancelledOrderItem::class)
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        return [
            'count' => $ordersCount->map(fn(TrendValue $value) => $value->aggregate),
            'sales' => $ordersSales->map(fn(TrendValue $value) => $value->aggregate),
            'returns' => $ordersReturns->map(fn(TrendValue $value) => $value->aggregate),
            'cancelled' => $ordersCancelled->map(fn(TrendValue $value) => $value->aggregate),
            'labels' => $ordersCount->map(fn(TrendValue $value) => $value->date),
        ];
    }

    public function getOrderStats($start = null, $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now();

        return [
            'total_orders' => Order::whereBetween('created_at', [$start, $end])->count(),
            'total_sales' => OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->sum('order_items.total'),
            'total_profit' => OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->sum('order_items.profit'),
            'total_returns' => ReturnOrderItem::whereBetween('created_at', [$start, $end])->sum('total'),
            'total_cancelled' => CancelledOrderItem::whereBetween('created_at', [$start, $end])->sum('total'),
            'average_order_value' => OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->avg('order_items.total') ?? 0,
        ];
    }

}
