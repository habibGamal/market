<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Notifications\Notify;
use App\Models\PurchaseInvoice;
use App\Services\PrintTemplateService;
use App\Models\YourModel; // Replace with your actual model

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/print/{model}/{id}', function (string $model,$id,PrintTemplateService $service) {
    $record = $model::findOrFail($id);
    Gate::authorize('view', $record);
    return $service->printPage($record);
})->name('print')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
