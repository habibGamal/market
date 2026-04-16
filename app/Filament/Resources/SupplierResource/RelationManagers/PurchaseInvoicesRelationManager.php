<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Filament\Exports\PurchaseInvoiceExporter;
use App\Filament\Resources\PurchaseInvoiceResource\Pages\ViewPurchaseInvoice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseInvoices';

    protected static ?string $title = 'فواتير المشتريات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('المجموع')
                    ->money('EGP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('الحالة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('execution_date')
                    ->label('تاريخ التنفيذ')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
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
                    ->exporter(PurchaseInvoiceExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->url(fn ($record) => ViewPurchaseInvoice::getUrl(['record' => $record])),
            ])
            ->bulkActions([]);
    }

}
