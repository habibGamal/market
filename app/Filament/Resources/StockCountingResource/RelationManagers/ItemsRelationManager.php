<?php

namespace App\Filament\Resources\StockCountingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'عناصر الجرد';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج'),
                Tables\Columns\TextColumn::make('old_packets_quantity')
                    ->label('عدد العبوات (قديم)'),
                Tables\Columns\TextColumn::make('old_piece_quantity')
                    ->label('عدد القطع (قديم)'),
                Tables\Columns\TextColumn::make('new_packets_quantity')
                    ->label('عدد العبوات (جديد)'),
                Tables\Columns\TextColumn::make('new_piece_quantity')
                    ->label('عدد القطع (جديد)'),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->money('egp'),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date(),
                Tables\Columns\TextColumn::make('total_diff')
                    ->label('الفرق')
                    ->money('egp'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}
