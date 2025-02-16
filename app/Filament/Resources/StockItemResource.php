<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockItemResource\Pages;
use App\Models\StockItem;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static ?string $modelLabel = 'مستوى المخزن';

    protected static ?string $pluralModelLabel = 'مستويات المخزن';


    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.id')
                    ->label('رقم المنتج'),
                Tables\Columns\TextColumn::make('product.barcode')
                    ->label('الباركود')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('اسم')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.packet_cost')
                    ->label('تكلفة العبوة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.packet_price')
                    ->label('سعر العبوة')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('cost_evaluation')
                //     ->label('التكلفة المقدرة')
                //     ->formatStateUsing(fn($state) => number_format($state, 2))
                //     // ->summarize(Sum::make())
                //     ,
                // Tables\Columns\TextColumn::make('price_evaluation')
                //     ->label('السعر المقدر')
                //     ->formatStateUsing(fn($state) => number_format($state, 2))
                //     // ->summarize(Sum::make())
                //     ,
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('product.brand', 'name')
                    ->searchable()
                    ->preload()
                    ->label('العلامة التجارية'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('product.category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('الفئة'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات المنتج')
                    ->columns(6)
                    ->schema([
                        ImageEntry::make('product.image')
                            ->label('صورة المنتج')
                            ->columnSpanFull(),
                        TextEntry::make('product.id')
                            ->label('رقم المنتج'),
                        TextEntry::make('product.barcode')
                            ->label('الباركود'),
                        TextEntry::make('product.name')
                            ->label('اسم المنتج'),
                        TextEntry::make('product.packet_cost')
                            ->label('تكلفة العبوة'),
                        TextEntry::make('product.packet_price')
                            ->label('سعر العبوة'),
                        TextEntry::make('product.piece_price')
                            ->label('سعر القطعة'),
                        TextEntry::make('product.expiration')
                            ->label('مدة الصلاحية'),
                        TextEntry::make('product.before_discount.packet_price')
                            ->label('سعر العبوة قبل الخصم'),
                        TextEntry::make('product.before_discount.piece_price')
                            ->label('سعر القطعة قبل الخصم'),
                        TextEntry::make('product.packet_to_piece')
                            ->label('عدد القطع في العبوة'),
                        TextEntry::make('product.brand.name')
                            ->label('العلامة التجارية'),
                        TextEntry::make('product.category.name')
                            ->label('الفئة'),
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockItems::route('/'),
            // 'create' => Pages\CreateStockItem::route('/create'),
            'view' => Pages\ViewStockItem::route('/{record}'),
            // 'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }
}
