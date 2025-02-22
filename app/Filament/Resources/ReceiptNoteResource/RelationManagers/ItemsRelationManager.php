<?php

namespace App\Filament\Resources\ReceiptNoteResource\RelationManagers;

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

    protected static ?string $title = 'محتوى اذن الاستلام';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Will be implemented based on your needs
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('سعر العبوة')
                    ->sortable()
                    ->visible(auth()->user()->can('show_costs_receipt::note')),
                Tables\Columns\TextColumn::make('quantityReleases')
                    ->label('تواريخ الانتاج')
                    ->formatStateUsing(function ($state, $record) {
                        return collect($record->quantityReleases)->map(function ($quantity, $date) {
                            return "{$date} : {$quantity}\r\n";
                        })->join(', ');
                    })
                ,
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
