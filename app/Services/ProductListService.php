<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductListService
{
    /**
     * Get products based on provided model and ID
     */
    public function getProducts(string $model, int $id)
    {
        return match($model) {
            'section' => $this->getSectionProducts($id),
            'category' => $this->getCategoryProducts($id),
            'brand' => $this->getBrandProducts($id),
            default => Product::query(),
        };
    }

    /**
     * Get search query for products
     */
    public function getSearchQuery(string $query = null)
    {
        $baseQuery = Product::query();

        if ($query) {
            $baseQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            });
        }

        return $baseQuery;
    }

    /**
     * Apply filters to product query
     */
    public function applyFilters($query)
    {
        // Apply category filters
        if (request()->has('categories')) {
            $categories = explode(',', request('categories'));
            $query->whereIn('category_id', $categories);
        }

        // Apply brand filters
        if (request()->has('brands')) {
            $brands = explode(',', request('brands'));
            $query->whereIn('brand_id', $brands);
        }

        // Apply price filters
        if (request()->has('min_price')) {
            $query->where('packet_price', '>=', request('min_price'));
        }

        if (request()->has('max_price')) {
            $query->where('packet_price', '<=', request('max_price'));
        }

        // Apply sorting
        $sortBy = request('sort_by', 'created_at');
        $sortDirection = request('sort_direction', 'desc');

        $allowedSortFields = ['created_at', 'name', 'packet_price'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    /**
     * Get categories for filtering with hierarchical structure
     */
    public function getFilterCategories($baseQuery): Collection
    {
        // Include all unique categories from products, along with their parent and sibling categories
        $productCategoryIds = $baseQuery->clone()->pluck('category_id')->unique();
        $allCategoryIds = collect();

        // First, add all direct product categories
        $allCategoryIds = $allCategoryIds->merge($productCategoryIds);

        // Get parent categories for the product categories
        $parentCategories = Category::whereIn('id', function($query) use ($productCategoryIds) {
            $query->select('parent_id')
                  ->from('categories')
                  ->whereIn('id', $productCategoryIds)
                  ->where('parent_id', '>', 0);
        })->pluck('id');

        // Add parent categories
        $allCategoryIds = $allCategoryIds->merge($parentCategories);

        // Get sibling categories (categories with same parent)
        $siblingCategories = Category::whereIn('parent_id', function($query) use ($productCategoryIds) {
            $query->select('parent_id')
                  ->from('categories')
                  ->whereIn('id', $productCategoryIds)
                  ->where('parent_id', '>', 0);
        })->pluck('id');

        // Add sibling categories
        $allCategoryIds = $allCategoryIds->merge($siblingCategories);

        // Get child categories of product categories
        $childCategories = Category::whereIn('parent_id', $productCategoryIds)->pluck('id');

        // Add child categories
        $allCategoryIds = $allCategoryIds->merge($childCategories);

        // Fetch all relevant categories with parent_id for nested structure
        return Category::whereIn('id', $allCategoryIds->unique())
                      ->select('id', 'name', 'parent_id')
                      ->get();
    }

    /**
     * Get all categories for filtering
     */
    public function getAllCategories(): Collection
    {
        // Get all categories with parent_id for hierarchical structure
        return Category::select('id', 'name', 'parent_id')->get();
    }

    /**
     * Get brands for filtering
     */
    public function getFilterBrands($baseQuery): Collection
    {
        return Brand::whereIn('id', $baseQuery->clone()->pluck('brand_id')->unique())
                   ->select('id', 'name')
                   ->get();
    }

    /**
     * Get all brands for filtering
     */
    public function getAllBrands(): Collection
    {
        return Brand::select('id', 'name')->get();
    }

    /**
     * Get section products
     */
    protected function getSectionProducts(int $id)
    {
        $section = Section::findOrFail($id);
        $sectionService = app(SectionService::class);
        return $sectionService->getProductsOfSection($section);
    }

    /**
     * Get category products
     */
    protected function getCategoryProducts(int $id)
    {
        $category = Category::findOrFail($id);
        return $category->products();
    }

    /**
     * Get brand products
     */
    protected function getBrandProducts(int $id)
    {
        $brand = Brand::findOrFail($id);
        return $brand->products();
    }
}
