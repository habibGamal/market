<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Filament\Exports\AccountantIssueNoteExporter;
use App\Models\AccountantIssueNote;
use App\Models\ReceiptNote;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountantIssueNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'receiptNotes';

    protected static ?string $title = 'اذون صرف النقدية';
    protected static ?string $modelLabel = 'اذن صرف نقدية';
    protected static ?string $pluralModelLabel = 'اذون صرف نقدية';

    public function getTableQuery(): ?Builder
    {
        return AccountantIssueNote::query()
            ->where('for_model_type', ReceiptNote::class)
            ->whereIn('for_model_id', function ($query) {
                $query->select('receipt_note_id')
                    ->from('purchase_invoices')
                    ->where('supplier_id', $this->getOwnerRecord()->id)
                    ->whereNotNull('receipt_note_id');
            })
            ->with([
                'forModel' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    ReceiptNote::class => ['purchaseInvoice.supplier'],
                ]),
                'officer',
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الإذن')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('forModel.purchaseInvoice.id')
                    ->label('رقم فاتورة المشتريات')
                    ->url(fn($record) => $record->forModel?->purchaseInvoice
                        ? \App\Filament\Resources\PurchaseInvoiceResource\Pages\ViewPurchaseInvoice::getUrl(['record' => $record->forModel->purchaseInvoice])
                        : null)
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('for_model_id')
                    ->label('رقم إذن الاستلام')
                    ->url(fn($record) => \App\Filament\Resources\ReceiptNoteResource::getUrl('view', ['record' => $record->for_model_id]))
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label('المدفوع')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('تصدير')
                    ->exporter(AccountantIssueNoteExporter::class)
                    ->modifyQueryUsing(fn(Builder $query) => $this->getTableQuery()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\AccountantIssueNoteResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([])
            ->emptyStateHeading('لا توجد اذون صرف نقدية')
            ->emptyStateDescription('لم يتم إنشاء أي اذون صرف نقدية لهذا المورد بعد.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
