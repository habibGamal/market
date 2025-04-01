<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Exports\CartItemsByProductsReportExporter;
use App\Filament\Resources\Reports\CartItemsByProductsReportResource\Pages;
use App\Filament\Resources\Reports\CartItemsByProductsReportResource\RelationManagers\CartItemsRelationManager;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;

class CartItemsByProductsReportResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $modelLabel = 'تقرير سلة المشتريات حسب المنتجات';

    protected static ?string $pluralModelLabel = 'تقارير سلة المشتريات حسب المنتجات';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cart_items_count')
                    ->label('عدد مرات الإضافة للسلة')
                    ->counts('cartItems')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cart_items_sum_packets_quantity')
                    ->label('مجموع العبوات في السلات')
                    ->sum('cartItems', 'packets_quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cart_items_sum_piece_quantity')
                    ->label('مجموع القطع في السلات')
                    ->sum('cartItems', 'piece_quantity')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CartItemsByProductsReportExporter::class),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات المنتج')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم المنتج'),
                        TextEntry::make('barcode')
                            ->label('الباركود'),
                        TextEntry::make('category.name')
                            ->label('الفئة'),
                        TextEntry::make('brand.name')
                            ->label('العلامة التجارية'),
                        TextEntry::make('packet_price')
                            ->label('سعر العبوة')
                            ->money('EGP'),
                        TextEntry::make('piece_price')
                            ->label('سعر القطعة')
                            ->money('EGP'),
                    ])->columns(3)
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItemsByProductsReports::route('/'),
            'view' => Pages\ViewCartItemsByProductsReport::route('/{record}'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            CartItemsRelationManager::class,
        ];
    }
}
