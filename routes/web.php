<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\ProductListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PlaceOrderController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Notifications\Templates\OrderTemplate;
use App\Services\NotificationService;
use App\Services\PrintTemplateService;
use Illuminate\Support\Facades\Gate;

// Public Routes
Route::get('/', [PageBuilderController::class, 'home']);
Route::get('/hot-deals', [PageBuilderController::class, 'hotDeals']);
Route::get('/product-list', [ProductListController::class, 'index']);
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
Route::get('/categories', function () {
    return Inertia::render('Categories/Index', [
        'categories' => Category::all()
    ]);
})->name('categories.index');
Route::get('/search', [ProductListController::class, 'search'])->name('search');

// Guest Routes (Unauthenticated)
Route::middleware('guest:customer')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::post('/forgot-password/otp/send', [ForgotPasswordController::class, 'send'])->name('password.otp.send');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Customer Authenticated Routes
Route::middleware(['auth:customer'])->group(function () {

    // OTP Verification Routes
    Route::get('/otp/verify', [OtpVerificationController::class, 'create'])->name('otp.verify');
    Route::post('/otp/send', [OtpVerificationController::class, 'sendOtp'])->name('otp.send');
    Route::post('/otp/verify', [OtpVerificationController::class, 'verify'])->name('otp.verify');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Routes that require phone verification
    Route::middleware(['customer.verified'])->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

        // Notification routes
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification'])->name('notifications.test');

        // Order routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [OrderController::class, 'placeOrder'])->name('orders.place');
        Route::get('/place-order', [OrderController::class, 'previewPlaceOrder'])->name('place-order.show');
    });
    Route::post('/subscribe', function () {
        $user = auth()->user();
        $user->updatePushSubscription(
            request('endpoint'),
            request('keys')['p256dh'],
            request('keys')['auth'],
            'aesgcm'
        );
        return response()->noContent();
    });
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/{item}', [CartController::class, 'updateQuantity'])->name('cart.update');
    Route::delete('/cart/{item}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'empty'])->name('cart.empty');
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin/User Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/print/{model}/{id}', function (string $model, $id, PrintTemplateService $service) {
        $record = $model::findOrFail($id);
        Gate::authorize('view', $record);
        return $service->printPage($record);
    })->name('print');

});

Route::get('/notify', function (NotificationService $notificationService) {
    // Use the OrderTemplate with our notification service
    $notificationService->sendToAll(
        new OrderTemplate(),
        [
            'order_id' => 1,
            'order_code' => 1
        ]
    );
    return response()->json(['sent' => true]);
});
