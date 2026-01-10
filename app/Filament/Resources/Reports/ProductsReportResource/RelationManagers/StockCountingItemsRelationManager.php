<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Filament\Exports\StockCountingItemsExporter;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class StockCountingItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockCountingItems';
    protected static ?string $title = 'جرد المنتج';
    protected static ?string $modelLabel = 'عنصر جرد';
    protected static ?string $pluralModelLabel = 'عناصر الجرد';

    #[Url]
    public $start;
    #[Url]
    public $end;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('stockCounting', function ($query) {
                    $query->where('status', InvoiceStatus::CLOSED);

                    if ($this->start && $this->end) {
                        $query->whereBetween('created_at', [$this->start, $this->end]);
                    }
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('stockCounting.id')
                    ->label('رقم إذن الجرد')
                    ->sortable(),
                Tables\Columns\TextColumn::make('old_packets_quantity')
                    ->label('عدد العبوات (قديم)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('old_piece_quantity')
                    ->label('عدد القطع (قديم)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_packets_quantity')
                    ->label('عدد العبوات (جديد)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_piece_quantity')
                    ->label('عدد القطع (جديد)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->money('EGP')
                    ->visible(fn() => auth()->user()->can('show_costs_stock::counting')),
                Tables\Columns\TextColumn::make('total_diff')
                    ->label('الفرق')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn() => auth()->user()->can('show_costs_stock::counting')),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockCounting.notes')
                    ->label('ملاحظات')
                    ->limit(50),
                Tables\Columns\TextColumn::make('stockCounting.created_at')
                    ->label('تاريخ الانشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير')
                    ->exporter(StockCountingItemsExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\StockCountingResource::getUrl('view', [
                        'record' => $record->stockCounting,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('لا يوجد جرد')
            ->emptyStateDescription('هذا المنتج ليس له جرد مسجل حتى الآن');
    }
}
