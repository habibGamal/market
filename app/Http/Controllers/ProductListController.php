<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\ProductListService;
use App\Services\SectionService;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ProductListController extends Controller
{
    public function __construct(
        protected SectionService $sectionService,
        protected ProductListService $productListService
    ) {}

    public function index()
    {
        $model = request('model');
        $id = request('id');

        if (!in_array($model, ['section', 'category', 'brand'])) {
            abort(404, 'نوع القائمة غير صالح');
        }

        // Get base query for products based on model and ID
        $baseQuery = $this->productListService->getProducts($model, $id)
        ->where('products.is_active', true);

        // Apply filters and get paginated results
        $query = $this->productListService->applyFilters($baseQuery->clone());
        $pagination = $query->paginate(10);

        // Get categories and brands for filtering
        $categories = $this->productListService->getFilterCategories($baseQuery);
        $brands = $this->productListService->getFilterBrands($baseQuery);

        // Get title based on model type
        $title = $this->getTitle($model, $id);

        return Inertia::render('Products/Section', [
            'title' => $title,
            'products' => inertia()->merge($pagination->items()),
            'pagination' => Arr::except($pagination->toArray(), ['data']),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    public function search()
    {
        $searchQuery = request('q');

        // Get base query for search
        $baseQuery = $this->productListService->getSearchQuery($searchQuery)->where('is_active', true);

        // Apply filters and get paginated results
        $query = $this->productListService->applyFilters($baseQuery);
        $pagination = $query->paginate(10);

        // Get all categories and brands for filtering
        $categories = $this->productListService->getAllCategories();
        $brands = $this->productListService->getAllBrands();

        return Inertia::render('Products/Search', [
            'title' => 'نتائج البحث',
            'query' => $searchQuery,
            'products' => inertia()->merge($pagination->items()),
            'pagination' => Arr::except($pagination->toArray(), ['data']),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Get title based on model type and ID
     */
    protected function getTitle(string $model, int $id): string
    {
        return match($model) {
            'section' => app(\App\Models\Section::class)::findOrFail($id)->title ?? 'القسم',
            'category' => Category::findOrFail($id)->name ?? 'الفئة',
            'brand' => Brand::findOrFail($id)->name ?? 'العلامة التجارية',
            default => 'المنتجات',
        };
    }
}
