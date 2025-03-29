<?php

namespace App\Services\Reports;

use App\Models\Area;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnOrderItem;
use App\Models\CancelledOrderItem;
use App\Services\OrdersStatsService;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrdersByAreasReportService
{
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $ordersSubQuery = DB::table('orders')
            ->select('customers.area_id')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customers.area_id');

        $orderItemsSubQuery = DB::table('order_items')
            ->select('customers.area_id')
            ->selectRaw('SUM(order_items.total) as total_sales')
            ->selectRaw('SUM(order_items.profit) as total_profit')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('orders.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customers.area_id');

        $returnsSubQuery = DB::table('return_order_items')
            ->select('customers.area_id')
            ->selectRaw('COALESCE(SUM(return_order_items.total), 0) as total_returns')
            ->selectRaw('COALESCE(SUM(return_order_items.profit), 0) as profit_returns')
            ->join('orders', 'orders.id', '=', 'return_order_items.order_id')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('return_order_items.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customers.area_id');

        $cancelsSubQuery = DB::table('cancelled_order_items')
            ->select('customers.area_id')
            ->selectRaw('COALESCE(SUM(cancelled_order_items.total), 0) as total_cancelled')
            ->join('orders', 'orders.id', '=', 'cancelled_order_items.order_id')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('cancelled_order_items.created_at', [$data['start_date'], $data['end_date']])
            ->groupBy('customers.area_id');

        return $query->addSelect([
            'areas.*',
            'orders.orders_count',
            'order_items.total_sales',
            DB::raw('order_items.total_profit - returns.profit_returns as total_profit'),
            'returns.total_returns',
            'cancels.total_cancelled'
        ])
            ->leftJoinSub($ordersSubQuery, 'orders', 'areas.id', '=', 'orders.area_id')
            ->leftJoinSub($orderItemsSubQuery, 'order_items', 'areas.id', '=', 'order_items.area_id')
            ->leftJoinSub($returnsSubQuery, 'returns', 'areas.id', '=', 'returns.area_id')
            ->leftJoinSub($cancelsSubQuery, 'cancels', 'areas.id', '=', 'cancels.area_id');
    }

    public function getBestAreaStats($start = null, $end = null, $query): array
    {
        $bestArea = $query->clone()->reorder()->orderByDesc('order_items.total_sales')->limit(1)->first();

        $totalAreasQuery = $query->clone()->whereHas('customers.orders', function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        });

        return [
            'best_area' => $bestArea ? [
                'name' => $bestArea->name,
                'orders_count' => $bestArea->orders_count ?? 0,
                'total_sales' => $bestArea->total_sales ?? 0,
            ] : null,
            'total_areas' => $totalAreasQuery->count(),
        ];
    }

    public function getAreaOrdersChartData($area, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = Order::query()
            ->whereHas('customer', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            });

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
                ->whereHas('order.customer', function ($query) use ($area) {
                    $query->where('area_id', $area->id);
                })
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

    public function getAreaReturnsChartData($area, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = ReturnOrderItem::query()
            ->whereHas('order.customer', function ($query) use ($area) {
                $query->where('area_id', $area->id);
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

    public function loadAreaStats(Area $area): Area
    {
        $statsService = app(OrdersStatsService::class);
        $orders = $statsService->getOrdersWithStats($area->orders());
        $stats = $statsService->calculateOrdersStats($orders);

        $area->orders_count = $stats['total_orders'];
        $area->total_sales = $stats['total_sales'];
        $area->total_profit = $stats['total_profit'];
        $area->total_returns = $stats['total_returns'];
        $area->total_cancelled = $stats['total_cancelled'];

        return $area;
    }

    public function getAreaCancelledOrdersChartData($area, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = CancelledOrderItem::query()
            ->whereHas('order.customer', function ($query) use ($area) {
                $query->where('area_id', $area->id);
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
