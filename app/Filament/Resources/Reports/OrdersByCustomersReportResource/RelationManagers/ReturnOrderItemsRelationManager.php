<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\RelationManagers;

use App\Enums\ReturnOrderStatus;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnItems';
    protected static ?string $title = 'المرتجعات';
    protected static ?string $modelLabel = 'مرتجع';
    protected static ?string $pluralModelLabel = 'المرتجعات';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('رقم الطلب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_price')
                    ->label('سعر العبوة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_price')
                    ->label('سعر القطعة')
                    ->money('EGP'),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_reason')
                    ->label('سبب الإرجاع')
                    ->limit(50),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ReturnOrderStatus::class),
                Tables\Filters\SelectFilter::make('driver')
                    ->label('السائق')
                    ->relationship('driver', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-arrow-uturn-left')
            ->emptyStateHeading('لا توجد مرتجعات')
            ->emptyStateDescription('هذا العميل ليس لديه مرتجعات حتى الآن');
    }
}
