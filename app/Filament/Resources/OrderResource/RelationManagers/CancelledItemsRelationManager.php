<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CancelledItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancelledItems';
    protected static ?string $title = 'الأصناف الملغاة';
    protected static ?string $modelLabel = 'صنف ملغي';
    protected static ?string $pluralModelLabel = 'الأصناف الملغاة';

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
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('الموظف المسؤول'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
