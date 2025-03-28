<?php
namespace App\Filament\Exports;

use App\Models\Driver;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriverExporter extends Exporter
{
    protected static ?string $model = Driver::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('معرف السائق'),
            ExportColumn::make('name')->label('اسم السائق'),
            ExportColumn::make('email')->label('البريد الإلكتروني'),
            ExportColumn::make('account.balance')->label('الرصيد'),
            ExportColumn::make('tasks_count')->label('عدد المهام')->counts('tasks'),
            ExportColumn::make('created_at')->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير السائقين وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
