<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnOrderItem;
use App\Models\WorkDay;
use App\Services\Stats\StockStatService;
use App\Services\WorkDayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyReportService
{
    public function __construct(
        private readonly WorkDayService $workDayService,
        private readonly StockStatService $stockStatService,
    ) {
    }

    public function getWorkDay(?string $date = null): WorkDay
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $workDay = WorkDay::where('day', $date)->first();
        if (!$workDay) {
            $workDay = $this->workDayService->getToday();
        }
        return $workDay;
    }

    public function getStockEvaluation(): array
    {
        $productIds = Product::pluck('id')->toArray();
        return [
            'cost' => $this->stockStatService->stockEvaluationByCostPrice($productIds) ?? 0,
            'price' => $this->stockStatService->stockEvaluationBySellPrice($productIds) ?? 0,
        ];
    }

    public function getAvailableStockEvaluation(): array
    {
        $result = Product::select(
            [
                DB::raw('SUM(products.packet_cost * (stock_items.piece_quantity - stock_items.unavailable_quantity - stock_items.reserved_quantity) / products.packet_to_piece) as total_cost'),
                DB::raw('SUM(products.packet_price * (stock_items.piece_quantity - stock_items.unavailable_quantity - stock_items.reserved_quantity) / products.packet_to_piece) as total_price'),
            ]
        )
            ->join('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->first();

        return [
            'cost' => $result->total_cost ?? 0,
            'price' => $result->total_price ?? 0,
        ];
    }

    public function getUnavailableStockEvaluation(): array
    {
        $result = Product::select(
            [
                DB::raw('SUM(products.packet_cost * (stock_items.unavailable_quantity) / products.packet_to_piece) as total_cost'),
                DB::raw('SUM(products.packet_price * (stock_items.unavailable_quantity) / products.packet_to_piece) as total_price'),
            ]
        )
            ->join('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->first();

        return [
            'cost' => $result->total_cost ?? 0,
            'price' => $result->total_price ?? 0,
        ];
    }

    public function getReservedStockEvaluation(): array
    {
        $result = Product::select(
            [
                DB::raw('SUM(products.packet_cost * (stock_items.reserved_quantity) / products.packet_to_piece) as total_cost'),
                DB::raw('SUM(products.packet_price * (stock_items.reserved_quantity) / products.packet_to_piece) as total_price'),
            ]
        )
            ->join('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->first();

        return [
            'cost' => $result->total_cost ?? 0,
            'price' => $result->total_price ?? 0,
        ];
    }

    public function getOrderStats(string $date): array
    {
        return [
            'total_orders' => Order::whereDate('created_at', $date)->count(),
            'total_returns' => ReturnOrderItem::whereDate('created_at', $date)->count(),
        ];
    }
}
