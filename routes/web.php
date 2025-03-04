<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\SearchController;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\BusinessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Notifications\Notify;
use App\Models\PurchaseInvoice;
use App\Services\PrintTemplateService;
use App\Services\HomePageBuilderService;

// API Routes
Route::prefix('api')->group(function () {
    Route::get('/search', SearchController::class);
    Route::get('/products', function () {
        $query = Product::with(['category', 'brand']);

        // Filtering
        if (request('categories')) {
            $categories = is_array(request('categories')) ? request('categories') : explode(',', request('categories'));
            $query->orWhereIn('category_id', $categories);
        }

        if (request('brands')) {
            $brands = is_array(request('brands')) ? request('brands') : explode(',', request('brands'));
            $query->orWhereIn('brand_id', $brands);
        }

        // Only show deals
        if (request('only_deals')) {
            $threshold = 10;
            $query->addSelect(
                [
                    'products.*',
                    DB::raw('COALESCE((CAST(JSON_EXTRACT(before_discount, "$.packet_price") AS DECIMAL(8,2)) - packet_price), 0) as discount'),
                ]
            )->orderBy('discount', 'desc');
        }

        // Search
        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        // Price range
        if (request('min_price')) {
            $query->where('price', '>=', request('min_price'));
        }
        if (request('max_price')) {
            $query->where('price', '<=', request('max_price'));
        }

        // Sorting
        $sortField = request('sort_by', 'created_at');
        $sortDirection = request('sort_direction', 'desc');
        $allowedSortFields = ['name', 'packet_price', 'created_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = request('per_page', 10);
        $limit = request('limit'); // For limited preview

        if ($limit) {
            $query->limit($limit);
        }
        return $query->paginate($perPage);
    });
});

// Main Routes
Route::get('/', function (HomePageBuilderService $homePageBuilder) {
    // For now, we'll use the first business type. In a real app, this would be determined by the domain, subdomain, or user selection
    $businessType = BusinessType::first();

    if (!$businessType) {
        abort(404, 'No business type configured');
    }

    $content = $homePageBuilder->getHomePageContent($businessType);

    return Inertia::render('Home', [
        'sliderImages' => $content['sliders']->map(fn($slider) => [
            'src' => $slider->image,
            'alt' => '',
            'href' => $slider->link
        ]),
        'announcements' => $content['announcements'],
        'sections' => $content['sections'],
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/notify', function () {
    $subscriptions = User::all();
    Notification::send($subscriptions, new Notify());
    return response()->json(['sent' => true]);
});

Route::post('/subscribe', function () {
    $user = auth()->user();

    $user->updatePushSubscription(
        request('endpoint'),
        request('publicKey'),
        request('authToken'),
        'aesgcm'
    );

    return response()->noContent();
})->middleware('auth');

Route::get('/print/{model}/{id}', function (string $model, $id, PrintTemplateService $service) {
    $record = $model::findOrFail($id);
    Gate::authorize('view', $record);
    return $service->printPage($record);
})->name('print')->middleware('auth');

Route::get('/products/{product}', function (Product $product) {
    return Inertia::render('Products/Show', [
        'product' => array_merge($product->toArray(), [
            'prices' => $product->prices,
            'isNew' => $product->is_new,
            'isDeal' => $product->is_deal,
            'category' => $product->category,
            'brand' => $product->brand,
        ])
    ]);
})->name('products.show');

Route::get('/products', function () {
    return Inertia::render('Products/Index');
})->name('products.index');

Route::get('/cart', function () {
    return Inertia::render('Cart/Index');
})->name('cart.index');

Route::get('/notifications', function () {
    return Inertia::render('Notifications/Index');
})->name('notifications.index');

Route::get('/categories', function () {
    return Inertia::render('Categories/Index', [
        'categories' => Category::all()
    ]);
})->name('categories.index');

Route::get('/hot-deals', function () {
    return Inertia::render('Products/HotDeals');
})->name('products.hot-deals');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
