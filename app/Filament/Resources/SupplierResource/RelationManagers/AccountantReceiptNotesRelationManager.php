<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Filament\Exports\AccountantReceiptNoteExporter;
use App\Models\AccountantReceiptNote;
use App\Models\IssueNote;
use App\Models\ReturnPurchaseInvoice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountantReceiptNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'issueNotes';

    protected static ?string $title = 'اذون استلام النقدية';
    protected static ?string $modelLabel = 'اذن استلام نقدية';
    protected static ?string $pluralModelLabel = 'اذون استلام نقدية';

    public function getTableQuery(): ?Builder
    {
        return AccountantReceiptNote::query()
            ->where('from_model_type', IssueNote::class)
            ->whereIn('from_model_id', function ($query) {
                $query->select('issue_note_id')
                    ->from('return_purchase_invoices')
                    ->where('supplier_id', $this->getOwnerRecord()->id)
                    ->whereNotNull('issue_note_id');
            })
            ->with([
                'fromModel' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    IssueNote::class => ['returnPurchaseInvoice.supplier'],
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
                Tables\Columns\TextColumn::make('fromModel.returnPurchaseInvoice.id')
                    ->label('رقم فاتورة مرتجع المشتريات')
                    ->url(fn($record) => $record->fromModel?->returnPurchaseInvoice
                        ? \App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages\ViewReturnPurchaseInvoice::getUrl(['record' => $record->fromModel->returnPurchaseInvoice])
                        : null)
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('from_model_id')
                    ->label('رقم إذن الصرف')
                    ->url(fn($record) => \App\Filament\Resources\IssueNoteResource::getUrl('view', ['record' => $record->from_model_id]))
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label('المبلغ المحصل')
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
                    ->exporter(AccountantReceiptNoteExporter::class)
                    ->modifyQueryUsing(fn(Builder $query) => $this->getTableQuery()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn($record) => \App\Filament\Resources\AccountantReceiptNoteResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([])
            ->emptyStateHeading('لا توجد اذون استلام نقدية')
            ->emptyStateDescription('لم يتم إنشاء أي اذون استلام نقدية لهذا المورد بعد.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
