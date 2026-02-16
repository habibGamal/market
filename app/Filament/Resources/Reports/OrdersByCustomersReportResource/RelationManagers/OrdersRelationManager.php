<?php

namespace App\Filament\Resources\Reports\OrdersByCustomersReportResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'الطلبات';
    protected static ?string $modelLabel = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->addSelect([
                    'items_profit' => \App\Models\OrderItem::selectRaw('COALESCE(SUM(profit), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                    'returns_total' => \App\Models\ReturnOrderItem::selectRaw('COALESCE(SUM(total), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                    'returns_profit' => \App\Models\ReturnOrderItem::selectRaw('COALESCE(SUM(profit), 0)')
                        ->whereColumn('order_id', 'orders.id'),
                ])
                ->with(['returnItems', 'items']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('إجمالي الطلب')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('netProfit')
                    ->label('صافي الربح')
                    ->money('EGP')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(items_profit - returns_profit - discount) {$direction}");
                    }),
                Tables\Columns\TextColumn::make('net_profit_percent')
                    ->label('نسبة صافي الربح')
                    ->getStateUsing(function (Order $record) {
                        $netTotal = $record->netTotal;
                        return $netTotal > 0 ? ($record->netProfit / $netTotal) * 100 : 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("
                            CASE
                                WHEN (total - returns_total - discount) > 0
                                THEN ((items_profit - returns_profit - discount) / (total - returns_total - discount)) * 100
                                ELSE 0
                            END {$direction}
                        ");
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('مندوب التسليم')
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
                    ->label('مندوب التسليم')
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
