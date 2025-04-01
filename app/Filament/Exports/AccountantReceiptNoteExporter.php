<?php

namespace App\Filament\Exports;

use App\Models\AccountantReceiptNote;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AccountantReceiptNoteExporter extends Exporter
{
    protected static ?string $model = AccountantReceiptNote::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الإذن'),
            ExportColumn::make('from_model_type')
                ->label('نوع المستند')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'App\\Models\\Driver' => 'مندوب تسليم',
                    'App\\Models\\IssueNote' => 'اذن صرف',
                    default => $state
                }),
            ExportColumn::make('from_model_id')
                ->label('رقم المستند'),
            ExportColumn::make('paid')
                ->label('المبلغ المحصل'),
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
        $body = 'تم اكتمال تصدير اذون استلام النقدية وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
