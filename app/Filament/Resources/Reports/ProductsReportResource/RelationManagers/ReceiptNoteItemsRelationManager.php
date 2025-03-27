<?php

namespace App\Filament\Resources\Reports\ProductsReportResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Filament\Resources\ReceiptNoteResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ReceiptNoteItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'receiptNoteItems';
    protected static ?string $title = 'مشتريات المنتج';
    protected static ?string $modelLabel = 'عنصر إذن الاستلام';
    protected static ?string $pluralModelLabel = 'عناصر إذن الاستلام';


    #[Url]
    public $start;
    #[Url]
    public $end;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->whereHas('receiptNote', function ($query) {
                    $query->where('note_type', ReceiptNoteType::PURCHASES)
                        ->where('status', InvoiceStatus::CLOSED);

                    if ($this->start && $this->end) {
                        $query->whereBetween('created_at', [$this->start, $this->end]);
                    }
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('receiptNote.id')
                    ->label('رقم الإذن')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packets_quantity')
                    ->label('عدد العبوات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('piece_quantity')
                    ->label('عدد القطع')
                    ->sortable(),
                Tables\Columns\TextColumn::make('packet_cost')
                    ->label('تكلفة العبوة')
                    ->money('EGP')
                    ->visible(fn() => auth()->user()->can('show_costs_receipt::note')),
                Tables\Columns\TextColumn::make('quantityReleases')
                    ->label('تواريخ الانتاج')
                    ->formatStateUsing(function ($state, $record) {
                        return collect($record->quantityReleases)->map(function ($quantity, $date) {
                            return "{$date} : {$quantity}";
                        })->join(', ');
                    }),
                Tables\Columns\TextColumn::make('receiptNote.status')
                    ->label('حالة الإذن')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receiptNote.created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => ReceiptNoteResource::getUrl('view', [
                        'record' => $record->receiptNote,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('لا توجد عناصر إذن استلام')
            ->emptyStateDescription('هذا المنتج ليس له عناصر إذن استلام حتى الآن');
    }
}
