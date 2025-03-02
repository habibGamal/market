<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\BrandsReportResource\Pages;
use App\Filament\Widgets\BrandsStatsOverview;
use App\Models\Brand;
use App\Services\Reports\BrandReportService;
use App\Traits\ReportsFilter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BrandsReportResource extends Resource
{
    use ReportsFilter;

    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير العلامة التجارية';

    protected static ?string $pluralModelLabel = 'تقارير العلامات التجارية';

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
                    ->sortable(),
            ])
            ->filters([
                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        return app(BrandReportService::class)->getFilteredQuery($query, $data);
                    })
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrandsReports::route('/'),
            'view' => Pages\ViewBrandsReport::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BrandsStatsOverview::class,
        ];
    }
}
