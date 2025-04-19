<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\InvoicesReportResource\Pages;
use App\Filament\Widgets\InvoiceStatsChart;
use App\Filament\Widgets\PurchaseInvoiceStats;
use App\Filament\Widgets\ReturnPurchaseInvoiceStats;
use App\Filament\Widgets\WasteInvoiceStats;
use App\Models\PurchaseInvoice;
use App\Services\Reports\InvoiceReportService;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = PurchaseInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'إدارة المشتريات';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'تقرير الفواتير';

    protected static ?string $pluralModelLabel = 'تقارير الفواتير';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_reports_purchase::invoice');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoicesReports::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            PurchaseInvoiceStats::class,
            ReturnPurchaseInvoiceStats::class,
            WasteInvoiceStats::class,
            InvoiceStatsChart::class,
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
        ];
    }
}
