<?php

namespace App\Filament\Driver\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;

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
                Grid::make([
                    'default' => 2,
                    'sm' => 2,
                    'lg' => 4,
                ])->schema([
                    Tables\Columns\TextColumn::make('product.name')
                        ->label('المنتج')
                        ->formatSateUsingLabelPrefix(),
                    Tables\Columns\TextColumn::make('total')
                        ->label('الإجمالي')
                        ->money('EGP')
                        ->formatSateUsingLabelPrefix(),
                    Stack::make([
                        Tables\Columns\TextColumn::make('packets_quantity')
                            ->label('عدد العبوات')
                            ->formatSateUsingLabelPrefix(),
                        Tables\Columns\TextColumn::make('packet_price')
                            ->label('سعر العبوة')
                            ->money('EGP')
                            ->formatSateUsingLabelPrefix(),
                    ]),
                    Stack::make([
                        Tables\Columns\TextColumn::make('piece_quantity')
                            ->label('عدد القطع')
                            ->formatSateUsingLabelPrefix(),
                        Tables\Columns\TextColumn::make('piece_price')
                            ->label('سعر القطعة')
                            ->money('EGP')
                            ->formatSateUsingLabelPrefix(),
                    ]),
                    Tables\Columns\TextColumn::make('officer.name')
                        ->label('الموظف المسؤول')
                        ->formatSateUsingLabelPrefix(),
                    Tables\Columns\TextColumn::make('notes')
                        ->label('ملاحظات')
                        ->limit(50)
                        ->formatSateUsingLabelPrefix(),
                ])
            ]);
    }
}
