<?php

namespace App\Filament\Exports;

use App\Models\PurchaseInvoice;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PurchaseInvoiceExporter extends Exporter
{
    protected static ?string $model = PurchaseInvoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الفاتورة'),
            ExportColumn::make('total')
                ->label('المجموع'),
            ExportColumn::make('raw_status')
                ->label('الحالة'),
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
        $body = 'تم اكتمال تصدير فاتورة الشراء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMilliseconds(10000);
    }
}
