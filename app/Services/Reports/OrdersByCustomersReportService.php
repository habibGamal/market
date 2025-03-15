<?php

namespace App\Services\Reports;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrdersByCustomersReportService
{
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $ordersSubQuery = DB::table('orders')
            ->select('customer_id')
            ->selectRaw('COUNT(DISTINCT id) as orders_count')
            ->whereBetween('created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customer_id');

        $orderItemsSubQuery = DB::table('order_items')
            ->select('orders.customer_id')
            ->selectRaw('SUM(order_items.total) as total_sales')
            ->selectRaw('SUM(order_items.profit) as total_profit')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('orders.customer_id');

        $returnsSubQuery = DB::table('return_order_items')
            ->select('orders.customer_id')
            ->selectRaw('COALESCE(SUM(return_order_items.total), 0) as total_returns')
            ->selectRaw('COALESCE(SUM(return_order_items.profit), 0) as profit_returns')
            ->join('orders', 'orders.id', '=', 'return_order_items.order_id')
            ->whereBetween('return_order_items.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('orders.customer_id');

        $cancelsSubQuery = DB::table('cancelled_order_items')
            ->select('orders.customer_id')
            ->selectRaw('COALESCE(SUM(cancelled_order_items.total), 0) as total_cancelled')
            ->join('orders', 'orders.id', '=', 'cancelled_order_items.order_id')
            ->whereBetween('cancelled_order_items.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('orders.customer_id');

        return $query->addSelect([
            'customers.*',
            'orders.orders_count',
            'order_items.total_sales',
            DB::raw('order_items.total_profit - returns.profit_returns as total_profit'),
            'returns.total_returns',
            'cancels.total_cancelled'
        ])
            ->leftJoinSub($ordersSubQuery, 'orders', 'customers.id', '=', 'orders.customer_id')
            ->leftJoinSub($orderItemsSubQuery, 'order_items', 'customers.id', '=', 'order_items.customer_id')
            ->leftJoinSub($returnsSubQuery, 'returns', 'customers.id', '=', 'returns.customer_id')
            ->leftJoinSub($cancelsSubQuery, 'cancels', 'customers.id', '=', 'cancels.customer_id');
    }

    public function getBestCustomerStats($start = null, $end = null,$query): array
    {
        $bestCustomer = $query->clone()->reorder()->orderByDesc('order_items.total_sales')->limit(1)->first();

        $totalCustomersQuery = $query->clone()->whereHas('orders', function($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        });

        return [
            'best_customer' => $bestCustomer ? [
                'name' => $bestCustomer->name,
                'orders_count' => $bestCustomer->orders_count,
                'total_sales' => $bestCustomer->total_sales,
            ] : null,
            'total_customers' => $totalCustomersQuery->count(),
        ];
    }

    public function loadCustomerStats(Customer $customer): Customer
    {
        $result = $customer->orders()
            ->withSum('items', 'profit')
            ->withSum('returnItems', 'profit')
            ->withSum('returnItems', 'total')
            ->get();

        $customer->orders_count = $customer->orders()->count();
        $customer->total_sales = $result->sum('total');
        $customer->total_profit = $result->sum(function ($order) {
            return $order->items_sum_profit - $order->return_items_sum_profit;
        });
        $customer->total_returns = $result->sum('return_items_sum_total');
        $customer->total_cancelled = $result->where('status', '=', 'cancelled')->sum('total');

        return $customer;
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

}
