<?php

namespace App\Filament\Exports;

use App\Models\StockCounting;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class StockCountingExporter extends Exporter
{
    protected static ?string $model = StockCounting::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الجرد'),
            ExportColumn::make('total_diff')
                ->label('إجمالي الفرق'),
            ExportColumn::make('status')
                ->label('الحالة')
                ->state(fn(StockCounting $record): string => $record->status->getLabel()),
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
        $body = 'تم اكتمال تصدير جرد المخزون وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
