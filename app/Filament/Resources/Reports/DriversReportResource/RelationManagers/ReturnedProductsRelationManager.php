<?php

namespace App\Filament\Resources\Reports\DriversReportResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReturnedProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnedProducts';

    protected static ?string $title = 'المنتجات المرتجعة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم المنتج')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                TextColumn::make('pivot.piece_quantity')
                    ->label('عدد القطع')
                    ->sortable()
            ]);
    }
}
