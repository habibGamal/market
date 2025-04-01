<?php

namespace App\Filament\Exports;

use App\Models\ReturnPurchaseInvoiceItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReturnPurchaseItemsExporter extends Exporter
{
    protected static ?string $model = ReturnPurchaseInvoiceItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('returnPurchaseInvoice.id')
                ->label('رقم الفاتورة'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات'),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('total')
                ->label('الإجمالي')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('returnPurchaseInvoice.supplier.name')
                ->label('المورد'),
            ExportColumn::make('returnPurchaseInvoice.officer.name')
                ->label('المسؤول'),
            ExportColumn::make('returnPurchaseInvoice.created_at')
                ->label('تاريخ الإرجاع'),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير مرتجعات المشتريات للمنتج وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
