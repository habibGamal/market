<?php

namespace App\Filament\Exports;

use App\Models\StockholderProfitExtraction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockholderProfitExtractionExporter extends Exporter
{
    protected static ?string $model = StockholderProfitExtraction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الإذن'),
            ExportColumn::make('stockholder.name')
                ->label('الشريك'),
            ExportColumn::make('profit')
                ->label('المبلغ'),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
            ExportColumn::make('officer.name')
                ->label('المسؤول'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير صرف أرباح الشركاء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
