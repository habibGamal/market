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
            ExportColumn::make('id')->label('معرف المنتج'),
            ExportColumn::make('name')->label('اسم المنتج'),
            ExportColumn::make('barcode')->label('الباركود'),
            ExportColumn::make('description')->label('الوصف'),
            ExportColumn::make('image')->label('الصورة'),
            ExportColumn::make('is_active')->label('نشط'),
            ExportColumn::make('can_sell_pieces')->label('بيع بالقطعة'),
            ExportColumn::make('packet_to_piece')->label('عدد القطع في العبوة'),
            ExportColumn::make('packet_alter_name')->label('الاسم البديل للعبوة'),
            ExportColumn::make('piece_alter_name')->label('الاسم البديل للقطعة'),
            ExportColumn::make('packet_cost')->label('تكلفة العبوة'),
            ExportColumn::make('packet_price')->label('سعر العبوة'),
            ExportColumn::make('piece_price')->label('سعر القطعة'),
            ExportColumn::make('before_discount.packet_price')->label('سعر العبوة قبل الخصم'),
            ExportColumn::make('before_discount.piece_price')->label('سعر القطعة قبل الخصم'),
            ExportColumn::make('expiration')->label('مدة الصلاحية'),
            ExportColumn::make('min_packets_stock_limit')->label('الحد الأدنى للمخزون (عبوات)'),
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


