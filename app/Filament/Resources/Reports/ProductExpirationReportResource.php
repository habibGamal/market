<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ProductExpirationReportResource\Pages;
use App\Models\StockItem;
use App\Services\Reports\ProductExpirationReportService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductExpirationReportResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير المنتجات قريبة الانتهاء';

    protected static ?string $modelLabel = 'تقرير المنتجات قريبة الانتهاء';

    protected static ?string $pluralModelLabel = 'تقارير المنتجات قريبة الانتهاء';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn() => app(ProductExpirationReportService::class)->getProductsWithExpirationInfo())
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand_name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_until_expiration')
                    ->label('الأيام المتبقية')
                    ->numeric()
                    ->sortable()
                    ->color(
                        fn($record) =>
                        $record->days_until_expiration <= 0 ? 'danger' :
                        ($record->days_until_expiration <= 30 ? 'warning' : 'success')
                    ),
            ])
            ->defaultSort('days_until_expiration', 'asc')
            ->emptyStateHeading('لا يوجد منتجات قريبة من انتهاء الصلاحية')
            ->emptyStateDescription('جميع المنتجات في المخزون صالحة ولم تتجاوز نصف مدة صلاحيتها')
            ->filters([
                SelectFilter::make('brand')
                    ->relationship('product.brand', 'name')
                    ->multiple()
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->relationship('product.category', 'name')
                    ->multiple()
                    ->label('الفئة')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductExpirationReport::route('/'),
        ];
    }
}
