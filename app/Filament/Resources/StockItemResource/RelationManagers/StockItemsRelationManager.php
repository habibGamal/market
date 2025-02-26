<?php

namespace App\Filament\Resources\StockItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockItems';

    protected static ?string $title = 'مخزون المنتج';

    protected static ?string $modelLabel = 'مخزون';

    protected static ?string $pluralModelLabel = 'المخزون';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('piece_quantity')
                    ->label('عدد القطع')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('unavailable_quantity')
                    ->label('الكمية غير المتاحة')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('reserved_quantity')
                    ->label('الكمية المحجوزة')
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unavailable_quantity')
                    ->label('الكمية غير المتاحة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reserved_quantity')
                    ->label('الكمية المحجوزة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date()
                    ->sortable(),
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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
