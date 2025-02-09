<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'منتجات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                Forms\Components\TextInput::make('image')
                    ->label('الصورة')
                    ->nullable(),
                Forms\Components\TextInput::make('barcode')
                    ->label('الباركود')
                    ->required(),
                Forms\Components\TextInput::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('packet_price')
                    ->label('سعر العبوة')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('piece_price')
                    ->label('سعر القطعة')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('expiration_duration')
                    ->label('مدة الصلاحية')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('expiration_unit')
                    ->label('وحدة الصلاحية')
                    ->options(\App\Enums\ExpirationUnit::values())
                    ->required(),
                Forms\Components\Textarea::make('before_discount')
                    ->label('قبل الخصم')
                    ->required(),
                Forms\Components\TextInput::make('packet_to_piece')
                    ->label('عدد القطع في العبوة')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('limits')
                    ->label('الحدود')
                    ->required(),
                Forms\Components\Select::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('الباركود')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration')
                    ->label('مدة الصلاحية')
                    ->sortable(['expiration_duration', 'expiration_unit']),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('العلامة التجارية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('الفئة')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
