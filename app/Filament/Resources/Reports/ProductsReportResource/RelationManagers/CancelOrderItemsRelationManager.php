<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Filament\Exports\CancelOrderItemsExporter;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class CancelOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancelledOrderItems';
    protected static ?string $title = 'الأصناف الملغاة';
    protected static ?string $modelLabel = 'صنف ملغي';
    protected static ?string $pluralModelLabel = 'الأصناف الملغاة';


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
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('الموظف المسؤول'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإلغاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('officer')
                    ->relationship('officer', 'name')
                    ->label('الموظف المسؤول')
                    ->multiple()
                    ->preload(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(CancelOrderItemsExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\OrderResource::getUrl('view', [
                        'record' => $record->order,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateIcon('heroicon-o-x-circle')
            ->emptyStateHeading('لا توجد أصناف ملغاة')
            ->emptyStateDescription('هذا المنتج ليس له أصناف ملغاة حتى الآن');
    }
}
