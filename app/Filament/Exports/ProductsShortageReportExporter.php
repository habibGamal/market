<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductsShortageReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم المنتج'),
            ExportColumn::make('barcode')
                ->label('الباركود'),
            ExportColumn::make('min_packets_stock_limit')
                ->label('الحد الأدنى للمخزون (عبوات)'),
            ExportColumn::make('packet_to_piece')
                ->label('عدد القطع في العبوة'),
            ExportColumn::make('available_pieces')
                ->label('الكمية المتوفرة (قطع)'),
            ExportColumn::make('brand.name')
                ->label('العلامة التجارية'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير المنتجات تحت الحد الأدنى وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
