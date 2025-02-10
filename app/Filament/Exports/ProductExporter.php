<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('اسم المنتج'),
            // ExportColumn::make('image')->label('صورة'),
            ExportColumn::make('barcode')->label('الباركود'),
            ExportColumn::make('packet_cost')->label('تكلفة العبوة'),
            ExportColumn::make('packet_price')->label('سعر العبوة'),
            ExportColumn::make('piece_price')->label('سعر القطعة'),
            ExportColumn::make('expiration')->label('مدة الصلاحية'),
            // ExportColumn::make('before_discount')->label('قبل الخصم'),
            ExportColumn::make('packet_to_piece')->label('عدد القطع في العبوة'),
            // ExportColumn::make('limits')->label('الحدود'),
            ExportColumn::make('brand.name')->label('العلامة التجارية'),
            ExportColumn::make('category.name')->label('الفئة'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير المنتجات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
