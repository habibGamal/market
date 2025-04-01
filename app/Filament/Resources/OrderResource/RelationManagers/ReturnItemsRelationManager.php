<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnItems';
    protected static ?string $title = 'المرتجعات';
    protected static ?string $modelLabel = 'مرتجع';
    protected static ?string $pluralModelLabel = 'المرتجعات';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج'),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات'),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع'),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                Tables\Columns\TextColumn::make('return_reason')
                    ->label('سبب الإرجاع'),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('مندوب التسليم'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
