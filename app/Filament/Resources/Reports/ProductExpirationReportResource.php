<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\ProductExpirationReportResource\Pages;
use App\Models\StockItem;
use App\Services\Reports\ProductExpirationReportService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\ProductExpirationExporter;
use Illuminate\Database\Eloquent\Model;

class ProductExpirationReportResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير المنتجات قريبة الانتهاء';

    protected static ?string $modelLabel = 'تقرير المنتجات قريبة الانتهاء';

    protected static ?string $pluralModelLabel = 'تقارير المنتجات قريبة الانتهاء';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_product_expire_report_product');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_product_expire_report_product');
    }

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
                    ->formatStateUsing(function ($state, StockItem $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
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
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ProductExpirationExporter::class)
            ])
            ->bulkActions([
                ExportBulkAction::make()
                    ->label('تصدير')
                    ->exporter(ProductExpirationExporter::class)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductExpirationReport::route('/'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
