<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\CategoriesReportExporter;
use App\Filament\Resources\Reports\CategoriesReportResource\Pages;
use App\Filament\Widgets\CategoriesStatsOverview;
use App\Models\Category;
use App\Services\Reports\CategoryReportService;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير الفئة';

    protected static ?string $pluralModelLabel = 'تقارير الفئات';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_report_category', Category::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_items_sum_piece_quantity')
                    ->label('كمية المبيعات')
                    ->color('success')
                    ->icon('heroicon-s-arrow-trending-up')
                    ->iconPosition(IconPosition::After)
                    ->sortable(),
                TextColumn::make('return_order_items_sum_piece_quantity')
                    ->label('كمية المرتجعات')
                    ->color('danger')
                    ->icon('heroicon-s-arrow-trending-down')
                    ->iconPosition(IconPosition::After)
                    ->sortable(),
                TextColumn::make('order_items_sum_total')
                    ->label('قيمة المبيعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('order_items_sum_profit')
                    ->label('الارباح')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn ()=> auth()->user()->can('view_profits_category', Category::class)),
            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        return app(CategoryReportService::class)->getFilteredQuery($query, $data);
                    })
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CategoriesReportExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(CategoriesReportExporter::class),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriesReports::route('/'),
            'view' => Pages\ViewCategoriesReport::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CategoriesStatsOverview::class,
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
        ];
    }
}
