<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Actions\Tables\CancelOrderItemsBulkAction;
use App\Filament\Actions\Tables\ReturnOrderItemsBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'الأصناف';
    protected static ?string $modelLabel = 'صنف';
    protected static ?string $pluralModelLabel = 'الأصناف';

    public function isReadOnly(): bool
    {
        return false;
    }

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
            ])
            ->bulkActions([
                CancelOrderItemsBulkAction::make()
                    ->failedAction(function ($arguments) {
                        $this->replaceMountedAction('forceCancel', $arguments);
                    })
                ,
                ReturnOrderItemsBulkAction::make(),
            ]);
    }

    public function forceCancelAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('force_cancel')
            ->requiresConfirmation()
            ->action(function (array $arguments) {

            });
    }

}
