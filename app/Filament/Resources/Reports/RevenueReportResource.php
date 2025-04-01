<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\RevenueReportResource\Pages;
use App\Filament\Widgets\RevenueStatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Models\Expense;
use App\Services\Reports\RevenueReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RevenueReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير الإيرادات والأرباح';

    protected static ?string $pluralModelLabel = 'تقارير الإيرادات والأرباح';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        // This report doesn't use a traditional table view
        // We'll display widgets instead
        return $table->paginated(false)
        // ->view('filament.resources.empty')
        ;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueReports::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            RevenueStatsOverview::class,
            RevenueChart::class,
        ];
    }
}
