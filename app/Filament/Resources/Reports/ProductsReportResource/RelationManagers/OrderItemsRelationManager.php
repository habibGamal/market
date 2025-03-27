<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';
    protected static ?string $title = 'مبيعات المنتج';
    protected static ?string $modelLabel = 'عنصر مباع';
    protected static ?string $pluralModelLabel = 'عناصر المبيعات';


    #[Url]
    public $start;
    #[Url]
    public $end;


    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('order', function ($query) {
                    if ($this->start && $this->end) {
                        $query->whereBetween('created_at', [$this->start, $this->end]);
                    }
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('رقم الطلب')
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
                Tables\Columns\TextColumn::make('profit')
                    ->label('الربح')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order.status')
                    ->query(
                        fn($query, $data) => !empty($data['values'])
                            ? $query->whereHas(
                                'order',
                                fn($query) => $query->whereIn('status', $data['values'])
                            )
                            : $query
                    )
                    ->label('حالة الطلب')
                    ->multiple()
                    ->options(\App\Enums\OrderStatus::toSelectArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\OrderResource::getUrl('view', [
                        'record' => $record->order,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('لا توجد مبيعات')
            ->emptyStateDescription('هذا المنتج ليس له مبيعات حتى الآن');
    }
}
