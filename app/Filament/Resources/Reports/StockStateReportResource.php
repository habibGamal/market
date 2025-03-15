<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\StockStateReportResource\Pages;
use App\Models\Product;
use App\Services\Reports\StockStateReportService;
use App\Filament\Widgets\StockStateStatsOverview;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockStateReportResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير حالة المخزون';

    protected static ?string $pluralModelLabel = 'تقارير حالة المخزون';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('available_stock')
                    ->label('كمية المتاح')
                    ->numeric()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('available_stock_cost')
                    ->label('تكلفة المتاح')
                    ->money('EGP')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('returned_stock')
                    ->label('كمية المرتجع من المشتريات')
                    ->numeric()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('returned_stock_cost')
                    ->label('تكلفة المرتجع من المشتريات')
                    ->money('EGP')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('waste_stock')
                    ->label('كمية الهالك')
                    ->numeric()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('waste_stock_cost')
                    ->label('تكلفة الهالك')
                    ->money('EGP')
                    ->color('danger')
                    ->sortable(),
            ])
            ->defaultSort('available_stock_cost', 'desc')
            ->modifyQueryUsing(function ($query) {
                return app(StockStateReportService::class)->getProductsWithStockInfo();
            })
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->multiple()
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->label('الفئة')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            StockStateStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockStateReports::route('/'),
        ];
    }
}
