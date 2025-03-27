<?php

namespace App\Filament\Exports;

use App\Models\AccountantIssueNote;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AccountantIssueNoteExporter extends Exporter
{
    protected static ?string $model = AccountantIssueNote::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الإذن'),
            ExportColumn::make('for_model_type')
                ->label('نوع المستند')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'App\\Models\\ReceiptNote' => 'اذن استلام مشتريات',
                    default => $state
                }),
            ExportColumn::make('for_model_id')
                ->label('رقم المستند'),
            ExportColumn::make('paid')
                ->label('المدفوع'),
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
        $body = 'تم اكتمال تصدير اذون الصرف النقدية وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }

}
