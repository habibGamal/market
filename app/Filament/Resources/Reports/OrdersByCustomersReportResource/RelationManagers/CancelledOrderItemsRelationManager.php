<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CancelledOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancelledItems';
    protected static ?string $title = 'الأصناف الملغاة';
    protected static ?string $modelLabel = 'صنف ملغي';
    protected static ?string $pluralModelLabel = 'الأصناف الملغاة';

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
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('الموظف المسؤول')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('officer')
                    ->label('الموظف المسؤول')
                    ->relationship('officer', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-x-circle')
            ->emptyStateHeading('لا توجد أصناف ملغاة')
            ->emptyStateDescription('هذا العميل ليس لديه أصناف ملغاة حتى الآن');
    }
}
