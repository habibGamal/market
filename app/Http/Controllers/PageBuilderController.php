<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use App\Models\Category;
use App\Services\HomePageBuilderService;
use App\Services\HotDealsPageBuilderService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class PageBuilderController extends Controller
{
    public function __construct(
        protected HomePageBuilderService $homePageBuilder,
        protected HotDealsPageBuilderService $hotDealsPageBuilder
    ) {}

    public function home()
    {
        // For now, we'll use the first business type. In a real app, this would be determined by the domain, subdomain, or user selection
        $businessType = BusinessType::first();

        if (!$businessType) {
            abort(404, 'لا يوجد نوع نشاط تجاري مضبوط');
        }

        $content = $this->homePageBuilder->getHomePageContent($businessType);

        return Inertia::render('Home', [
            'sliderImages' => $content['sliders']->map(fn($slider) => [
                'src' => $slider->image,
                'alt' => '',
                'href' => $slider->link
            ]),
            ...$content,
            'announcements' => $content['announcements'],
            'categories' => Category::all(),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function hotDeals()
    {
        $businessType = BusinessType::first();

        if (!$businessType) {
            abort(404, 'لا يوجد نوع نشاط تجاري مضبوط');
        }

        $content = $this->hotDealsPageBuilder->getHotDealsContent($businessType);
        return Inertia::render('Products/HotDeals', [
            ...$content,
        ]);
    }
}
