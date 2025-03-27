<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('الرقم'),
            ExportColumn::make('expenseType.name')
                ->label('نوع المصروف'),
            ExportColumn::make('value')
                ->label('القيمة'),
            ExportColumn::make('notes')
                ->label('ملاحظات'),
            ExportColumn::make('approvedBy.name')
                ->label('تمت الموافقة من'),
            ExportColumn::make('accountant.name')
                ->label('المحاسب'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير المصروفات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
