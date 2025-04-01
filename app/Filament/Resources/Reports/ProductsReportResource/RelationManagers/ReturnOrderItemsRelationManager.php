<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Enums\ReturnOrderStatus;
use App\Filament\Exports\ReturnOrderItemsExporter;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ReturnOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnOrderItems';
    protected static ?string $title = 'مرتجعات المنتج';
    protected static ?string $modelLabel = 'مرتجع';
    protected static ?string $pluralModelLabel = 'المرتجعات';


    #[Url]
    public $start;
    #[Url]
    public $end;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->when($this->start && $this->end, function ($query) {
                    $query->whereBetween('created_at', [$this->start, $this->end]);
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
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_reason')
                    ->label('سبب الإرجاع')
                    ->limit(50),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('مندوب التسليم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإرجاع')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ReturnOrderStatus::class)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('driver')
                    ->label('مندوب التسليم')
                    ->relationship('driver', 'name')
                    ->preload()
                    ->multiple()
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(ReturnOrderItemsExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\OrderResource::getUrl('view', [
                        'record' => $record->order,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateIcon('heroicon-o-arrow-uturn-left')
            ->emptyStateHeading('لا توجد مرتجعات')
            ->emptyStateDescription('هذا المنتج ليس له مرتجعات حتى الآن');
    }
}
