<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\ProductsReportExporter;
use App\Filament\Resources\Reports\ProductsReportResource\Pages;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\ReturnOrderItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\CancelOrderItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\ReceiptNoteItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\ReturnPurchaseItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\WasteItemsRelationManager;
use App\Filament\Resources\Reports\ProductsReportResource\RelationManagers\StockCountingItemsRelationManager;
use App\Filament\Widgets\ProductsStatsOverview;
use App\Models\Product;
use App\Services\Reports\ProductReportService;
use App\Traits\ReportsFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductsReportResource extends Resource implements HasShieldPermissions
{
    use ReportsFilter;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'تقرير المنتج';

    protected static ?string $pluralModelLabel = 'تقارير المنتجات';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_product_report_product');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_product_report_product');
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
                TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_items_sum_piece_quantity')
                    ->label('كمية المبيعات')
                    ->formatStateUsing(function ($state, Product $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
                    ->color('success')
                    ->icon('heroicon-s-arrow-trending-up')
                    ->iconPosition(IconPosition::After)
                    ->sortable(),
                TextColumn::make('return_order_items_sum_piece_quantity')
                    ->label('كمية المرتجعات')
                    ->formatStateUsing(function ($state, Product $record) {
                        $packets = $state / $record->packet_to_piece;
                        return "{$state} قطعة = {$packets} عبوة";
                    })
                    ->color('danger')
                    ->icon('heroicon-s-arrow-trending-down')
                    ->iconPosition(IconPosition::After)
                    ->sortable(),
                TextColumn::make('order_items_sum_total')
                    ->label('قيمة المبيعات')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('order_items_sum_profit')
                    ->label('ارباح المنتج')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn () => auth()->user()->can('view_profits_product')),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('category')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('report_filter')
                    ->form(static::filtersForm())
                    ->baseQuery(function (Builder $query, array $data): Builder {
                        // if ($data['start_date'] == null)
                        // dd($data);
                        return app(ProductReportService::class)->getFilteredQuery($query, $data);
                    })
            ])
            ->filtersFormColumns(3)
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ProductsReportExporter::class),
            ])
            ->actions([
                // ...existing code...
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات المنتج')
                    ->columns(6)
                    ->schema([
                        ImageEntry::make('image')
                            ->label('صورة المنتج')
                            ->columnSpanFull(),
                        TextEntry::make('id')
                            ->label('رقم المنتج'),
                        TextEntry::make('barcode')
                            ->label('الباركود'),
                        TextEntry::make('name')
                            ->label('اسم المنتج'),
                        TextEntry::make('packet_cost')
                            ->label('تكلفة العبوة'),
                        TextEntry::make('packet_price')
                            ->label('سعر العبوة'),
                        TextEntry::make('piece_price')
                            ->label('سعر القطعة'),
                        TextEntry::make('expiration')
                            ->label('مدة الصلاحية'),
                        TextEntry::make('before_discount.packet_price')
                            ->label('سعر العبوة قبل الخصم'),
                        TextEntry::make('before_discount.piece_price')
                            ->label('سعر القطعة قبل الخصم'),
                        TextEntry::make('packet_to_piece')
                            ->label('عدد القطع في العبوة'),
                        TextEntry::make('brand.name')
                            ->label('العلامة التجارية'),
                        TextEntry::make('category.name')
                            ->label('الفئة'),
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            ReturnOrderItemsRelationManager::class,
            CancelOrderItemsRelationManager::class,
            ReceiptNoteItemsRelationManager::class,
            ReturnPurchaseItemsRelationManager::class,
            WasteItemsRelationManager::class,
            StockCountingItemsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ProductsStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductsReports::route('/'),
            'view' => Pages\ViewProductsReport::route('/{record}'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
