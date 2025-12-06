<?php

namespace App\Filament\Resources;

use App\Filament\Exports\StockItemExporter;
use App\Filament\Resources\StockItemResource\Pages;
use App\Filament\Resources\StockItemResource\RelationManagers;
use App\Models\Product;
use App\Models\StockItem;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockItemResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'إدارة المخزن';

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

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

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_stock_levels_product');
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()->can('view_stock_levels_product');
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
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم المنتج'),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_items_sum_piece_quantity')
                    ->sum('stockItems', 'piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->formatStateUsing(fn($record) => number_format(
                        $record->packets_quantity,
                        2
                    ))
                    ->label('عدد العبوات')
                    ,
                Tables\Columns\TextColumn::make('packets_and_pieces')
                    ->label('العبوات والقطع')
                    ->state(function ($record) {
                        $totalPieces = $record->stock_items_sum_piece_quantity ?? 0;
                        $packetToPiece = $record->packet_to_piece ?? 1;

                        $packets = floor($totalPieces / $packetToPiece);
                        $pieces = $totalPieces % $packetToPiece;

                        if ($packets > 0 && $pieces > 0) {
                            return "{$packets} عبوة و {$pieces} قطعة";
                        } elseif ($packets > 0) {
                            return "{$packets} عبوة";
                        } else {
                            return "{$pieces} قطعة";
                        }
                    }),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->label('العلامة التجارية'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('الفئة'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make('export')
                    ->label('تصدير')
                    ->exporter(StockItemExporter::class),
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
            RelationManagers\StockItemsRelationManager::class,
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

    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
