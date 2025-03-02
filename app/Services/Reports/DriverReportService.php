<?php

namespace App\Services\Reports;

use App\Enums\DriverStatus;
use App\Enums\ReturnOrderStatus;
use App\Models\Driver;
use App\Models\DriverTask;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DriverReportService
{
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        return $query->select([
            'users.id',
            'users.name',
            'users.email',
            'users.created_at',
            'users.updated_at',
            'rt.total as total_returns',
            DB::raw('COUNT(DISTINCT CASE WHEN driver_tasks.status = "received" THEN driver_tasks.id END) as pending_orders_count'),
            DB::raw('SUM(CASE WHEN orders.status = "out_for_delivery" THEN orders.total ELSE 0 END) as out_for_delivery_total'),
        ])
            ->leftJoin('driver_tasks', 'users.id', '=', 'driver_tasks.driver_id')
            ->leftJoin('orders', 'driver_tasks.order_id', '=', 'orders.id')
            ->leftJoinSub(
                \DB::table('driver_returned_products as rt')
                    ->select([
                        'rt.driver_id',
                        DB::raw('SUM((rt.packets_quantity + rt.piece_quantity/ products.packet_to_piece) * products.packet_cost) as total'),
                    ])
                    ->join('products', 'rt.product_id', '=', 'products.id')
                    ->groupBy('rt.driver_id'),
                'rt',
                'users.id',
                '=',
                'rt.driver_id'
            )
            ->driversOnly()
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.updated_at', 'rt.total');
    }

    public function getDeliveriesChartData($driver, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = DriverTask::query()
            ->where('driver_id', $driver->id)
            ->where('driver_tasks.status', DriverStatus::DONE);

        $deliveriesCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();

        $deliveriesTotals = Trend::query($query->clone()->join('orders', 'driver_tasks.order_id', '=', 'orders.id'))
            ->dateColumn('driver_tasks.created_at')
            ->between($start, $end)
            ->perDay()
            ->sum('orders.total');

        return [
            'labels' => $deliveriesCount->map(fn(TrendValue $value) => $value->date),
            'totals' => $deliveriesTotals->map(fn(TrendValue $value) => $value->aggregate),
            'quantities' => $deliveriesCount->map(fn(TrendValue $value) => $value->aggregate),
        ];
    }

    public function getReturnsChartData($driver, $start = null, $end = null): array
    {
        $start = $start ? Carbon::parse($start) : now()->startOfMonth();
        $end = $end ? Carbon::parse($end) : now();

        $query = ReturnOrderItem::query()
            ->where('driver_id', $driver->id)
            ->where('status', ReturnOrderStatus::RECEIVED_FROM_CUSTOMER);

        $returnsCount = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->count();

        $returnsTotals = Trend::query($query->clone())
            ->between($start, $end)
            ->perDay()
            ->sum('total');

        return [
            'labels' => $returnsCount->map(fn(TrendValue $value) => $value->date),
            'totals' => $returnsTotals->map(fn(TrendValue $value) => $value->aggregate),
            'quantities' => $returnsCount->map(fn(TrendValue $value) => $value->aggregate),
        ];
    }
}
