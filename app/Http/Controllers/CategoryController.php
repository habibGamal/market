<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        // Get the business type ID from the authenticated customer or use the first one as default
        $businessTypeId = null;

        if (auth()->guard('customer')->check()) {
            // User is authenticated with customer guard
            $businessTypeId = auth()->guard('customer')->user()->business_type_id;
        } else {
            // User is not authenticated, use the first business type as default
            $businessTypeId = \App\Models\BusinessType::first()->id ?? null;
        }
        // dd($businessTypeId);
        // Get categories for this business type
        $categories = Category::where('parent_id', '-1')
            ->where('is_active', true)
            ->when($businessTypeId, function ($query) use ($businessTypeId) {
                $query->whereHas('businessTypes', function ($query) use ($businessTypeId) {
                    $query->where('business_type_id', $businessTypeId);
                });
            })
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Display the specified category with its brands.
     */
    public function show(Category $category)
    {
        // Only show active categories
        if (!$category->is_active) {
            abort(404);
        }

        // Get brands with products in this category
        $brandsWithProducts = Brand::where('is_active', true)->whereHas('products', function ($query) use ($category) {
            $query->whereIn('category_id', $category->children()->where('is_active', true)->pluck('id'))
                ->orWhere('category_id', $category->id);
        })->get();

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'brands' => $brandsWithProducts
        ]);
    }
}
