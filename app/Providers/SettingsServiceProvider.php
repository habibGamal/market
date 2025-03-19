<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService();
        });

        // Register a 'settings' alias
        $this->app->alias(SettingsService::class, 'settings');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
