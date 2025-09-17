<?php

namespace App\Services\Reports;

use App\Models\AccountantIssueNote;
use App\Models\AccountantReceiptNote;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchaseInvoice;
use App\Models\ReturnOrderItem;
use App\Models\ReturnPurchaseInvoice;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class RevenueReportService
{
    /**
     * Get filtered query for revenue reporting
     */
    public function getFilteredQuery(Builder $query, array $data): Builder
    {
        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : null;
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : null;

        return $query;
    }

    /**
     * Get net sales revenue
     */
    public function getNetSalesRevenue($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        // Total sales from orders
        $totalSales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', \App\Enums\OrderStatus::DELIVERED)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum('order_items.total');

        // Total returns
        $totalReturns = ReturnOrderItem::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        // Net sales = Total sales - Returns
        return $totalSales - $totalReturns;
    }
    /**
     * Get net sales revenue
     */
    public function getOrderDiscounts($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $totalDiscounts = Order::query()
            ->where('orders.status', \App\Enums\OrderStatus::DELIVERED)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum('orders.discount');


        return $totalDiscounts;
    }

    /**
     * Get cost of goods sold
     */
    public function getCostOfGoodsSold($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        // Cost of items sold (based on profit calculation)
        $soldItemsCost = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', \App\Enums\OrderStatus::DELIVERED)
            ->select(DB::raw('SUM(order_items.total - order_items.profit) as cost'))
            ->first()
            ->cost ?? 0;

        // Subtract cost of returned items
        $returnedItemsCost = ReturnOrderItem::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(total - profit) as cost'))
            ->first()
            ->cost ?? 0;

        return $soldItemsCost - $returnedItemsCost;
    }

    /**
     * Get gross profit
     */
    public function getGrossProfit($startDate = null, $endDate = null): float
    {
        // Gross profit = Net sales - Cost of goods sold
        return $this->getNetSalesRevenue($startDate, $endDate) - $this->getCostOfGoodsSold($startDate, $endDate);
    }

    /**
     * Get total expenses
     */
    public function getTotalExpenses($startDate = null, $endDate = null): float
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        // Approved operating expenses
        $operatingExpenses = Expense::query()
            ->whereNotNull('approved_by')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('value');

        // Purchase expenses
        // $purchaseIssueNotes = AccountantIssueNote::query()
        //     ->where('for_model_type', PurchaseInvoice::class)
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->sum('paid');

        return $operatingExpenses;
    }

    /**
     * Get net profit
     */
    public function getNetProfit($startDate = null, $endDate = null): float
    {
        // Net profit = Gross profit - Total expenses
        return $this->getGrossProfit($startDate, $endDate) - $this->getTotalExpenses($startDate, $endDate);
    }

    /**
     * Get revenue chart data for trend visualization
     */
    public function getRevenueChartData($startDate = null, $endDate = null, int $days = 30): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subDays($days);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        // Sales trend
        $salesTrend = Trend::query(
            OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
        )
            ->between($startDate, $endDate)
            ->dateColumn('orders.created_at')
            ->perDay()
            ->sum('order_items.total');

        // Returns trend
        $returnsTrend = Trend::query(ReturnOrderItem::query())
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('total');

        // Expenses trend
        $expensesTrend = Trend::query(Expense::query()->whereNotNull('approved_by'))
            ->between($startDate, $endDate)
            ->perDay()
            ->sum('value');

        // Calculate net sales and net profit for each day
        $netSales = [];
        $grossProfits = [];
        $netProfits = [];

        for ($i = 0; $i < count($salesTrend); $i++) {
            // Calculate cost of goods sold for this day's sales
            $daySales = $salesTrend[$i]->aggregate ?? 0;
            $dayReturns = $returnsTrend[$i]->aggregate ?? 0;
            $dayExpenses = $expensesTrend[$i]->aggregate ?? 0;

            // Net sales = Sales - Returns
            $dayNetSales = $daySales - $dayReturns;
            $netSales[] = $dayNetSales;

            // Estimate COGS as 70% of net sales (this is a simplification)
            // In a real implementation, you would need to get actual COGS data
            $dayCogs = $dayNetSales * 0.7;
            $dayGrossProfit = $dayNetSales - $dayCogs;
            $grossProfits[] = $dayGrossProfit;

            // Net profit = Gross profit - Expenses
            $dayNetProfit = $dayGrossProfit - $dayExpenses;
            $netProfits[] = $dayNetProfit;
        }

        return [
            'labels' => $salesTrend->map(fn(TrendValue $value) => $value->date),
            'sales' => $salesTrend->map(fn(TrendValue $value) => $value->aggregate),
            'returns' => $returnsTrend->map(fn(TrendValue $value) => $value->aggregate),
            'expenses' => $expensesTrend->map(fn(TrendValue $value) => $value->aggregate),
            'net_sales' => $netSales,
            'gross_profits' => $grossProfits,
            'net_profits' => $netProfits,
        ];
    }

    /**
     * Get all revenue statistics in a single call
     */
    public function getAllStats($startDate = null, $endDate = null): array
    {
        $netSales = $this->getNetSalesRevenue($startDate, $endDate);
        $totalDiscounts = $this->getOrderDiscounts($startDate, $endDate);
        $cogs = $this->getCostOfGoodsSold($startDate, $endDate);
        // $grossProfit = $this->getGrossProfit($startDate, $endDate);
        $grossProfit = $netSales - $cogs - $totalDiscounts;
        $totalExpenses = $this->getTotalExpenses($startDate, $endDate);
        // $netProfit = $this->getNetProfit($startDate, $endDate);
        $netProfit = $grossProfit - $totalExpenses;

        // Calculate gross profit margin percentage
        $grossProfitMargin = $netSales ? ($grossProfit / $netSales) * 100 : 0;

        // Calculate net profit margin percentage
        $netProfitMargin = $netSales ? ($netProfit / $netSales) * 100 : 0;

        return [
            'net_sales' => $netSales,
            'total_discounts' => $totalDiscounts,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'net_profit_margin' => $netProfitMargin,
        ];
    }
}
