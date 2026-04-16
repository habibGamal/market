<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Filament\Exports\ReturnPurchaseInvoiceExporter;
use App\Filament\Resources\ReturnPurchaseInvoiceResource\Pages\ViewReturnPurchaseInvoice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class ReturnPurchaseInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'returnPurchaseInvoices';

    protected static ?string $title = 'فواتير مرتجعات المشتريات';

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
                    ->exporter(ReturnPurchaseInvoiceExporter::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->url(fn ($record) => ViewReturnPurchaseInvoice::getUrl(['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
