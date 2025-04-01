<?php

namespace App\Filament\Exports;

use App\Models\ReturnOrderItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReturnOrderItemExporter extends Exporter
{
    protected static ?string $model = ReturnOrderItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('الرقم'),
            ExportColumn::make('order.id')
                ->label('رقم الطلب'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات الكلي'),
            ExportColumn::make('packet_price')
                ->label('سعر العبوة'),
            ExportColumn::make('piece_quantity')
                ->label('عدد القطع الكلي'),
            ExportColumn::make('piece_price')
                ->label('سعر القطعة'),
            ExportColumn::make('total')
                ->label('المجموع'),
            ExportColumn::make('return_reason')
                ->label('سبب الإرجاع'),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
            ExportColumn::make('status')
                ->label('الحالة')
                ->state(fn(ReturnOrderItem $record): string => $record->status->getLabel()),
            ExportColumn::make('driver.name')
                ->label('مندوب التسليم'),
            ExportColumn::make('order.customer.name')
                ->label('اسم العميل'),
            ExportColumn::make('order.customer.phone')
                ->label('رقم الهاتف'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير المرتجعات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
