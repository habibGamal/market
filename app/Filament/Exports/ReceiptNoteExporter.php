<?php

namespace App\Filament\Exports;

use App\Models\ReceiptNote;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReceiptNoteExporter extends Exporter
{
    protected static ?string $model = ReceiptNote::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الإذن'),
            ExportColumn::make('total')
                ->label('المجموع')
                ->formatStateUsing(fn(string $state): string => auth()->user()->can('show_costs_receipt::note') ? $state : '*****'),
            ExportColumn::make('raw_status')
                ->label('الحالة'),
            ExportColumn::make('raw_note_type')
                ->label('نوع الإذن'),
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
        $body = 'تم اكتمال تصدير اذن الاستلام وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

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
