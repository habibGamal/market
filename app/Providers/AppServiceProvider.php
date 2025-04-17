<?php

namespace App\Providers;

use App\Jobs\ExportCsv;
use App\Jobs\ImportCsv;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Actions\Exports\Jobs\ExportCsv as BaseExportCsv;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;
use Illuminate\Support\Str;
use Filament\Infolists\Components\TextEntry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseExportCsv::class, ExportCsv::class);
        $this->app->bind(BaseImportCsv::class, ImportCsv::class);
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

        Column::macro('formatSateUsingLabelPrefix', function () {
            return $this->formatStateUsing(function (Column $column, $state) {
                return new HtmlString('<span class="font-medium">' . $column->getLabel() . '</span>: ' . $state);
            });
        });


        TextColumn::configureUsing(function (TextColumn $textColumn): void {
            $textColumn->timezone('Africa/Cairo');
        });

        TextEntry::configureUsing(function (TextEntry $textEntry): void {
            $textEntry->timezone('Africa/Cairo');
        });

        DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
            $dateTimePicker->timezone('Africa/Cairo');
        });

        DatePicker::configureUsing(function (DatePicker $dateTimePicker): void {
            $dateTimePicker->timezone('UTC');
        });

        // FilamentShield::configurePermissionIdentifierUsing(
        //     fn($resource) => Str::of($resource)
        //     ->afterLast('Resources\\')
        //     ->before('Resource')
        //     ->replace('\\', '')
        //     ->snake()
        //     ->replace('_', '::')
        // );

    }
}
