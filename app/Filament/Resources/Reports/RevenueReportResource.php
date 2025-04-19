<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\RevenueReportResource\Pages;
use App\Filament\Widgets\RevenueStatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Models\Expense;
use App\Services\Reports\RevenueReportService;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RevenueReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'إدارة الحسابات';
    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = 'تقرير الإيرادات والأرباح';

    protected static ?string $pluralModelLabel = 'تقارير الإيرادات والأرباح';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_revenue_reports_expense');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        // This report doesn't use a traditional table view
        // We'll display widgets instead
        return $table->paginated(false);
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


    public static function getPermissionPrefixes(): array
    {
        return [
        ];
    }
}
