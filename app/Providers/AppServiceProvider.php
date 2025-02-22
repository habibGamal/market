<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Model::unguard();

        FilamentColor::register([
            'yellow' => Color::Yellow,
            'red' => Color::Red,
            'blue' => Color::Blue,
            'orange' => Color::Orange,
            'green' => Color::Green,
        ]);

        Column::macro('formatSateUsingLabelPrefix', function() {
            return $this->formatStateUsing(function(Column $column, $state) {
                return new HtmlString('<span class="font-medium">' . $column->getLabel() . '</span>: ' . $state);
            });
        });
    }
}
