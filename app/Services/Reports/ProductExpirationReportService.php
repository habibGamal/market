<?php

namespace App\Services\Reports;

use App\Models\StockItem;
use App\Enums\ExpirationUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductExpirationReportService
{
    public function getProductsWithExpirationInfo(): Builder
    {
        return StockItem::query()
            ->select([
                'stock_items.*',
                'products.name as product_name',
                'products.packet_to_piece as packet_to_piece',
                'products.expiration_duration',
                'products.expiration_unit',
                'brands.name as brand_name',
                'categories.name as category_name',
                DB::raw('CASE
                    WHEN products.expiration_unit = "' . ExpirationUnit::DAY->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration DAY)
                    WHEN products.expiration_unit = "' . ExpirationUnit::WEEK->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration * 7 DAY)
                    WHEN products.expiration_unit = "' . ExpirationUnit::MONTH->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration MONTH)
                    WHEN products.expiration_unit = "' . ExpirationUnit::YEAR->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration YEAR)
                END as expiration_date'),
                DB::raw('DATEDIFF(
                    CASE
                        WHEN products.expiration_unit = "' . ExpirationUnit::DAY->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration DAY)
                        WHEN products.expiration_unit = "' . ExpirationUnit::WEEK->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration * 7 DAY)
                        WHEN products.expiration_unit = "' . ExpirationUnit::MONTH->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration MONTH)
                        WHEN products.expiration_unit = "' . ExpirationUnit::YEAR->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration YEAR)
                    END,
                    CURRENT_DATE()
                ) as days_until_expiration')
            ])
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereRaw('DATEDIFF(
                CASE
                    WHEN products.expiration_unit = "' . ExpirationUnit::DAY->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration DAY)
                    WHEN products.expiration_unit = "' . ExpirationUnit::WEEK->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration * 7 DAY)
                    WHEN products.expiration_unit = "' . ExpirationUnit::MONTH->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration MONTH)
                    WHEN products.expiration_unit = "' . ExpirationUnit::YEAR->value . '" THEN DATE_ADD(stock_items.release_date, INTERVAL products.expiration_duration YEAR)
                END,
                CURRENT_DATE()
            ) <= CASE
                WHEN products.expiration_unit = "' . ExpirationUnit::DAY->value . '" THEN products.expiration_duration / 2
                WHEN products.expiration_unit = "' . ExpirationUnit::WEEK->value . '" THEN products.expiration_duration * 7 / 2
                WHEN products.expiration_unit = "' . ExpirationUnit::MONTH->value . '" THEN products.expiration_duration * 30 / 2
                WHEN products.expiration_unit = "' . ExpirationUnit::YEAR->value . '" THEN products.expiration_duration * 365 / 2
            END')
            ->where('piece_quantity', '>', 0)
            ->orderBy('days_until_expiration');
    }
}
