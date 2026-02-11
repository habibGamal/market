<?php

namespace App\Services;

use App\Models\BusinessType;
use App\Models\Section;
use App\Models\Product;
use App\Models\OrderItem;
use App\Enums\SectionLocation;
use App\Enums\SectionType;
use App\Enums\VirturalSectionNames;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SectionService
{

    public function getProductsOfSection(Section $section): Builder
    {
        if ($section->section_type === SectionType::VIRTUAL) {
            if ($section->title === VirturalSectionNames::TREND->value) {
                $trendingProducts = $this->getTrendingProducts($section->business_type_id);

                // Check if trending products exist
                if ($this->queryIsEmpty($trendingProducts)) {
                    return $this->getFallbackProducts($section->business_type_id);
                }

                return $trendingProducts;
            }

            if ($section->title === VirturalSectionNames::RECOMMENDATION->value && auth()->check()) {
                $recommendedProducts = $this->getRecommendedProducts(auth()->id(), $section->business_type_id);

                // Check if recommended products exist
                if ($this->queryIsEmpty($recommendedProducts)) {
                    return $this->getFallbackProducts($section->business_type_id);
                }

                return $recommendedProducts;
            }

            // Return empty query for recommendation section when user is not logged in
            if ($section->title === VirturalSectionNames::RECOMMENDATION->value) {
                return Product::query()->where('id', 0);
            }
        }

        $productsQuery = Product::query()
            ->withActiveRelations()
            ->select('products.*')
            ->join('sectionables', function ($join) use ($section) {
                $join->on('products.id', '=', 'sectionables.sectionable_id')
                    ->where('sectionables.section_id', $section->id)
                    ->where('sectionables.sectionable_type', Product::class);
            });

        // Add products from associated brands
        if ($section->brands->isNotEmpty()) {
            $productsQuery->union(
                Product::query()
                    ->withActiveRelations()
                    ->select('products.*')
                    ->whereIn('brand_id', $section->brands->pluck('id'))
            );
        }

        // Add products from associated categories
        if ($section->categories->isNotEmpty()) {
            $productsQuery->union(
                Product::query()
                    ->withActiveRelations()
                    ->select('products.*')
                    ->whereIn('category_id', $section->categories->pluck('id'))
            );
        }

        return $productsQuery;
    }

    /**
     * Check if query would return empty results
     */
    protected function queryIsEmpty(Builder $query): bool
    {
        return $query->limit(1)->count() === 0;
    }

    /**
     * Get fallback products (most popular products)
     */
    protected function getFallbackProducts(int $businessTypeId): Builder
    {
        return Product::query()
            ->withActiveRelations()
            ->select('products.*')
            ->inRandomOrder() // Randomize fallback products
            ->whereHas('category.businessTypes', function ($query) use ($businessTypeId) {
                $query->where('business_type_id', $businessTypeId);
            });
    }

    protected function getTrendingProducts(int $businessTypeId): Builder
    {
        $endDate = now();
        $startDate = $endDate->copy()->subDays(7);

        // Using a subquery approach to first get trending product IDs
        $trendingProductsSubquery = DB::table('products')
            ->select(
                'products.id',
                DB::raw('SUM(order_items.packets_quantity * products.packet_to_piece + order_items.piece_quantity) /
                DATEDIFF("' . $endDate->format('Y-m-d') . '", "' . $startDate->format('Y-m-d') . '") as trend_score')
            )
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('customers.business_type_id', $businessTypeId)
            ->whereBetween('orders.created_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d H:i:s')])
            ->groupBy('products.id')
            ->orderByDesc('trend_score');

        // Join back to products to get all product fields
        return Product::query()
            ->withActiveRelations()
            ->select('products.*')
            ->joinSub($trendingProductsSubquery, 'trending', function ($join) {
                $join->on('products.id', '=', 'trending.id');
            })
            ->orderBy('trending.trend_score', 'desc');
    }

    protected function getRecommendedProducts(int $customerId, int $businessTypeId): Builder
    {
        // Get customer's purchase history categories and brands
        $customerPreferences = OrderItem::query()
            ->select([
                'products.category_id',
                'products.brand_id',
                DB::raw('COUNT(*) as purchase_count')
            ])
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customerId)
            ->groupBy('products.category_id', 'products.brand_id')
            ->orderByDesc('purchase_count')
            ->limit(3)
            ->get();

        if ($customerPreferences->isEmpty()) {
            // If no purchase history, return popular products in their business type
            return Product::query()
                ->withActiveRelations()
                ->select('products.*')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->where('customers.business_type_id', $businessTypeId)
                ->groupBy('products.id')
                ->orderByDesc(DB::raw('COUNT(order_items.id)'));
        }

        // Get products matching customer's preferred categories and brands
        $query = Product::query()->withActiveRelations()->where(function ($query) use ($customerPreferences) {
            foreach ($customerPreferences as $preference) {
                $query->orWhere(function ($q) use ($preference) {
                    $q->where('category_id', $preference->category_id)
                        ->orWhere('brand_id', $preference->brand_id);
                });
            }
        });

        return $query;
    }

}
