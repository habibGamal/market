<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ProductListController extends Controller
{
    public function __construct(
        protected SectionService $sectionService
    ) {}

    public function index()
    {
        $model = request('model');
        $id = request('id');

        if (!in_array($model, ['section', 'category', 'brand'])) {
            abort(404, 'نوع القائمة غير صالح');
        }

        $query = match($model) {
            'section' => $this->getSectionProducts($id),
            'category' => $this->getCategoryProducts($id),
            'brand' => $this->getBrandProducts($id),
        };

        $baseQuery = $query->clone();

        // Apply filters
        if (request()->has('categories')) {
            $categories = explode(',', request('categories'));
            $query->whereIn('category_id', $categories);
        }

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

        $pagination = $query->clone()->paginate(10);

        // Get all categories and brands for filters
        $categories = Category::whereIn('id', $baseQuery->clone()->pluck('category_id')->unique())->select('id', 'name')->get();
        $brands = Brand::whereIn('id', $baseQuery->clone()->pluck('brand_id')->unique())->select('id', 'name')->get();

        return Inertia::render('Products/Section', [
            'title' => $this->title,
            'products' => inertia()->merge($pagination->items()),
            'pagination' => Arr::except($pagination->toArray(), ['data']),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    public function search()
    {
        $query = request('q');
        $baseQuery = Product::query();

        if ($query) {
            $baseQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            });
        }

        // Apply filters
        if (request()->has('categories')) {
            $categories = explode(',', request('categories'));
            $baseQuery->whereIn('category_id', $categories);
        }

        if (request()->has('brands')) {
            $brands = explode(',', request('brands'));
            $baseQuery->whereIn('brand_id', $brands);
        }

        // Apply price filters
        if (request()->has('min_price')) {
            $baseQuery->where('packet_price', '>=', request('min_price'));
        }

        if (request()->has('max_price')) {
            $baseQuery->where('packet_price', '<=', request('max_price'));
        }

        // Apply sorting
        $sortBy = request('sort_by', 'created_at');
        $sortDirection = request('sort_direction', 'desc');

        $allowedSortFields = ['created_at', 'name', 'packet_price'];
        if (in_array($sortBy, $allowedSortFields)) {
            $baseQuery->orderBy($sortBy, $sortDirection);
        }

        $pagination = $baseQuery->paginate(10);

        // Get all categories and brands for filters
        $categories = Category::select('id', 'name')->get();
        $brands = Brand::select('id', 'name')->get();

        return Inertia::render('Products/Search', [
            'title' => 'نتائج البحث',
            'query' => $query,
            'products' => inertia()->merge($pagination->items()),
            'pagination' => Arr::except($pagination->toArray(), ['data']),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    protected string $title = '';

    protected function getSectionProducts($id)
    {
        $section = Section::findOrFail($id);
        $this->title = $section->title;
        return $this->sectionService->getProductsOfSection($section);
    }

    protected function getCategoryProducts($id)
    {
        $category = Category::findOrFail($id);
        $this->title = $category->name;
        return $category->products();
    }

    protected function getBrandProducts($id)
    {
        $brand = Brand::findOrFail($id);
        $this->title = $brand->name;
        return $brand->products();
    }
}
