<?php

namespace App\Filament\Exports;

use App\Models\StockItem;
use App\Services\Reports\ProductExpirationReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ProductExpirationExporter extends Exporter
{
    protected static ?string $model = StockItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_name')
                ->label('اسم المنتج'),
            ExportColumn::make('brand_name')
                ->label('العلامة التجارية'),
            ExportColumn::make('category_name')
                ->label('الفئة'),
            ExportColumn::make('piece_quantity')
                ->label('الكمية (قطع)'),
            ExportColumn::make('packet_quantity')
                ->label('الكمية (عبوات)')
                ->state(fn (StockItem $record): string =>
                    number_format($record->piece_quantity / $record->packet_to_piece, 2)
                ),
            ExportColumn::make('release_date')
                ->label('تاريخ الإنتاج'),
            ExportColumn::make('expiration_date')
                ->label('تاريخ الانتهاء'),
            ExportColumn::make('days_until_expiration')
                ->label('الأيام المتبقية'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير المنتجات قريبة الانتهاء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
