<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Enums\InvoiceStatus;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class WasteItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'wasteItems';
    protected static ?string $title = 'الهالك من المنتج';
    protected static ?string $modelLabel = 'هالك';
    protected static ?string $pluralModelLabel = 'الهالك';

    #[Url]
    public $start;
    #[Url]
    public $end;
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('waste', function ($query) {
                    $query->whereHas('issueNote', function ($query) {
                        $query->where('status', InvoiceStatus::CLOSED);

                        if ($this->start && $this->end) {
                            $query->whereBetween('created_at', [$this->start, $this->end]);
                        }
                    });
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('waste.id')
                    ->label('رقم مستند الهالك')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->money('EGP')
                    ->visible(fn() => auth()->user()->can('show_costs_waste')),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_cost')
                    ->label('تكلفة القطعة')
                    ->money('EGP')
                    ->visible(fn() => auth()->user()->can('show_costs_waste')),
                Tables\Columns\TextColumn::make('total')
                    ->label('إجمالي التكلفة')
                    ->money('EGP')
                    ->sortable()
                    ->visible(fn() => auth()->user()->can('show_costs_waste')),
                Tables\Columns\TextColumn::make('release_date')
                    ->label('تاريخ الإنتاج')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('waste.notes')
                    ->label('سبب الهالك')
                    ->limit(50),
                Tables\Columns\TextColumn::make('waste.created_at')
                    ->label('تاريخ الانشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\WasteResource::getUrl('view', [
                        'record' => $record->waste,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-trash')
            ->emptyStateHeading('لا يوجد هالك')
            ->emptyStateDescription('هذا المنتج ليس له هالك مسجل حتى الآن');
    }
}
