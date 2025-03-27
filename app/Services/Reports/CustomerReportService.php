<?php

namespace App\Services\Reports;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ReturnOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerReportService
{
    /**
     * Get customer's order and return statistics for a specified date range
     *
     * @param Customer $customer
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getCustomerStats(Customer $customer, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(3)->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // Get order statistics
        $orders = Order::where('customer_id', $customer->id)
            ->whereBetween('created_at', [$startDate, $endDate])->get();

        $orderStats = [
            'total_orders' => $orders->count(),
            'total_orders_amount' => $orders->sum('net_total'),
        ];

        // Get return statistics
        $returnsQuery = ReturnOrderItem::query()
            ->join('orders', 'return_order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->whereBetween('return_order_items.created_at', [$startDate, $endDate]);

        $returnStats = [
            'total_returns' => $returnsQuery->count(),
            'total_returns_amount' => $returnsQuery->sum('return_order_items.total'),
        ];

        // Get monthly statistics
        $monthlyStats = $this->getMonthlyStats($customer->id, $startDate, $endDate);

        return [
            'order_stats' => $orderStats,
            'return_stats' => $returnStats,
            'monthly_stats' => $monthlyStats,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get monthly statistics for orders and returns
     *
     * @param int $customerId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getMonthlyStats(int $customerId, Carbon $startDate, Carbon $endDate): array
    {
        // Monthly orders
        $monthlyOrders = Order::where('customer_id', $customerId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                '*',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
            )
            ->get()
            ->groupBy(
                function ($item) {
                    return $item->year . '-' . $item->month;
                }
            )
            ->map(function ($group) {
                return (object) [
                    'year' => $group->first()->year,
                    'month' => $group->first()->month,
                    'count' => $group->count(),
                    'total_amount' => $group->sum('net_total')
                ];
            })
            ->values()
            ->sortBy('year')
            ->sortBy('month')
            ;

        // Monthly returns
        $monthlyReturns = ReturnOrderItem::join('orders', 'return_order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customerId)
            ->whereBetween('return_order_items.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(return_order_items.created_at) as year'),
                DB::raw('MONTH(return_order_items.created_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(return_order_items.total) as total_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Format the data for frontend
        $formattedMonthlyStats = [];

        foreach ($monthlyOrders as $orderData) {
            $monthKey = $orderData->year . '-' . str_pad($orderData->month, 2, '0', STR_PAD_LEFT);

            if (!isset($formattedMonthlyStats[$monthKey])) {
                $formattedMonthlyStats[$monthKey] = [
                    'month' => $monthKey,
                    'order_count' => 0,
                    'order_amount' => 0,
                    'return_count' => 0,
                    'return_amount' => 0
                ];
            }

            $formattedMonthlyStats[$monthKey]['order_count'] = $orderData->count;
            $formattedMonthlyStats[$monthKey]['order_amount'] = $orderData->total_amount;
        }

        foreach ($monthlyReturns as $returnData) {
            $monthKey = $returnData->year . '-' . str_pad($returnData->month, 2, '0', STR_PAD_LEFT);

            if (!isset($formattedMonthlyStats[$monthKey])) {
                $formattedMonthlyStats[$monthKey] = [
                    'month' => $monthKey,
                    'order_count' => 0,
                    'order_amount' => 0,
                    'return_count' => 0,
                    'return_amount' => 0
                ];
            }

            $formattedMonthlyStats[$monthKey]['return_count'] = $returnData->count;
            $formattedMonthlyStats[$monthKey]['return_amount'] = $returnData->total_amount;
        }

        // Sort by month and convert to array
        ksort($formattedMonthlyStats);
        return array_values($formattedMonthlyStats);
    }
}
