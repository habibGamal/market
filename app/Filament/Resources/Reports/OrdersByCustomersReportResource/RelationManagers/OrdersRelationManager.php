<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\RelationManagers;

use App\Enums\OrderStatus;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'الطلبات';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('إجمالي الطلب')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('صافي الربح')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(OrderStatus::class),
                Tables\Filters\SelectFilter::make('driver')
                    ->label('السائق')
                    ->relationship('driver', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('لا توجد طلبات')
            ->emptyStateDescription('هذا العميل ليس لديه طلبات حتى الآن');
    }
}
