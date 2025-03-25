<?php

namespace App\Filament\Resources\Reports\CartItemsByCustomersReportResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';
    protected static ?string $title = 'عناصر السلة';
    protected static ?string $modelLabel = 'عنصر سلة';
    protected static ?string $pluralModelLabel = 'عناصر السلة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.packet_price')
                    ->label('سعر العبوة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.piece_price')
                    ->label('سعر القطعة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('لا توجد عناصر في السلة')
            ->emptyStateDescription('هذا العميل ليس لديه عناصر في السلة حتى الآن');
    }
}
