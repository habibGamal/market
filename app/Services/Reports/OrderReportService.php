<?php

namespace App\Services\Reports;

use App\Models\CancelledOrderItem;
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
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        return $query->select([
            'customers.id',
            'customers.name',
            'customers.phone',
            'customers.address',
            'customers.created_at',
            'customers.updated_at',
            DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
            DB::raw('SUM(order_items.total) as total_sales'),
            DB::raw('SUM(order_items.profit) as total_profit'),
            DB::raw('COALESCE(SUM(return_order_items.total), 0) as total_returns'),
            DB::raw('COALESCE(SUM(cancelled_order_items.total), 0) as total_cancelled')
        ])
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('return_order_items', 'orders.id', '=', 'return_order_items.order_id')
            ->leftJoin('cancelled_order_items', 'orders.id', '=', 'cancelled_order_items.order_id')
            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.address', 'customers.created_at', 'customers.updated_at');
    }

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

    public function getCustomerOrdersChartData($customer, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = Order::query()->where('customer_id', $customer->id);

        $ordersCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();

        $ordersSales = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        $ordersProfit = Trend::query(
            OrderItem::query()
                ->where('orders.customer_id', $customer->id)
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
        )
            ->between($start, $end)
            ->dateColumn('orders.created_at')
            ->perDay()
            ->sum('profit');

        return [
            'labels' => $ordersCount->map(fn(TrendValue $value) => $value->date),
            'totals' => $ordersSales->map(fn(TrendValue $value) => $value->aggregate),
            'profits' => $ordersProfit->map(fn(TrendValue $value) => $value->aggregate),
            'quantities' => $ordersCount->map(fn(TrendValue $value) => $value->aggregate),
        ];
    }

    public function getCustomerReturnsChartData($customer, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = ReturnOrderItem::query()->whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });

        $returnsTotals = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->sum('total');


        $returnsCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();

        return [
            'labels' => $returnsCount->map(fn(TrendValue $value) => $value->date),
            'totals' => $returnsTotals->map(fn(TrendValue $value) => $value->aggregate),
            'quantities' => $returnsCount->map(fn(TrendValue $value) => $value->aggregate),
        ];
    }

    public function getCustomerCancelsChartData($customer, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = ReturnOrderItem::query()->whereHas('order', function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        });

        $cancelsTotals = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->sum('total');


        $cancelsCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();
        return [
            'labels' => $cancelsCount->map(fn(TrendValue $value) => $value->date),
            'totals' => $cancelsTotals->map(fn(TrendValue $value) => $value->aggregate),
            'quantities' => $cancelsCount->map(fn(TrendValue $value) => $value->aggregate),
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

    public function getCustomerStats($start = null, $end = null): array
    {
        $start = $start ?? now()->startOfMonth();
        $end = $end ?? now();

        return DB::select("
            SELECT
                c.id,
                c.name as customer_name,
                COUNT(DISTINCT o.id) as orders_count,
                SUM(oi.total) as total_sales,
                SUM(oi.profit) as total_profit,
                COALESCE(SUM(ri.total), 0) as total_returns,
                COALESCE(SUM(ci.total), 0) as total_cancelled
            FROM customers c
            LEFT JOIN orders o ON o.customer_id = c.id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            LEFT JOIN return_order_items ri ON ri.order_id = o.id
            LEFT JOIN cancelled_order_items ci ON ci.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY c.id, c.name
            ORDER BY total_sales DESC
            LIMIT 10
        ", [$start, $end]);
    }
}
