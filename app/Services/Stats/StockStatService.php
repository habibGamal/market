<?php

namespace App\Services\Stats;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockStatService
{
    public function stockEvaluationBySellPrice(array $stockItemIds): float | null
    {
        return Product::select(DB::raw('sum(products.packet_price * (stock_items.piece_quantity / products.packet_to_piece)) as total_price'))
            ->leftJoin('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->whereIn('products.id', $stockItemIds)
            ->get()
            ->first()
            ->total_price;
    }

    public function stockEvaluationByCostPrice(array $stockItemIds): float | null
    {
        return Product::select(DB::raw('sum(products.packet_cost * (stock_items.piece_quantity / products.packet_to_piece)) as total_price'))
            ->leftJoin('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->whereIn('products.id', $stockItemIds)
            ->get()
            ->first()
            ->total_price;
    }
}
