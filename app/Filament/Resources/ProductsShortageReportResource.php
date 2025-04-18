<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductsShortageReportExporter;
use App\Filament\Resources\ProductsShortageReportResource\Pages;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductsShortageReportResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?string $navigationLabel = 'تقرير المنتجات تحت الحد الأدنى';
    protected static ?string $modelLabel = 'تقرير المنتجات تحت الحد الأدنى';

    protected static ?string $pluralModelLabel = 'تقارير المنتجات تحت الحد الأدنى';

    protected static ?string $slug = 'products-shortage-reports';

    protected static ?int $navigationSort = 9;

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_shortage_report_product');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_shortage_report_product');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query) {
                $stockSubQuery = \DB::table('stock_items')
                    ->select(
                        'product_id',
                        \DB::raw('COALESCE(SUM(piece_quantity - unavailable_quantity - reserved_quantity), 0) as available_pieces')
                    )
                    ->groupBy('product_id');

                return Product::leftJoinSub($stockSubQuery, 'stock_summary', function ($join) {
                    $join->on('products.id', '=', 'stock_summary.product_id');
                })
                    ->where('available_pieces', '<', \DB::raw('min_packets_stock_limit * packet_to_piece'));
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('min_packets_stock_limit')
                    ->label('الحد الأدنى للمخزون (عبوات)')
                    ->sortable(),
                TextColumn::make('packet_to_piece')
                    ->label('عدد القطع في العبوة')
                    ->sortable(),
                TextColumn::make('available_pieces')
                    ->label('الكمية المتوفرة (قطع)')
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable(),
            ])
            ->defaultSort('available_pieces', 'asc')
            ->emptyStateHeading('لا يوجد منتجات تحت الحد الأدنى')
            ->actions([])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ProductsShortageReportExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ProductsShortageReportExporter::class),
                ]),
            ])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductsShortageReport::route('/'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
