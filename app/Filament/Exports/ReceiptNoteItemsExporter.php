<?php

namespace App\Filament\Exports;

use App\Models\ReceiptNoteItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReceiptNoteItemsExporter extends Exporter
{
    protected static ?string $model = ReceiptNoteItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('receiptNote.id')
                ->label('رقم إذن الاستلام'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات'),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('piece_quantity')
                ->label('عدد القطع'),
            ExportColumn::make('total')
                ->label('الإجمالي')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('receiptNote.officer.name')
                ->label('المسؤول'),
            ExportColumn::make('receiptNote.created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('quantityReleases')
                ->label('تاريخ الإنتاج')
                ->state(function ($record) {
                    return collect($record->quantityReleases)->map(function ($quantity, $date) {
                        return "{$date} : {$quantity}";
                    })->join(', ');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير مشتريات المنتج وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
