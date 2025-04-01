<?php

namespace App\Filament\Exports;

use App\Models\DriverTask;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriversReportTasksExporter extends Exporter
{
    protected static ?string $model = DriverTask::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم المهمة'),
            ExportColumn::make('order.id')
                ->label('رقم الطلب'),
            ExportColumn::make('order.customer.name')
                ->label('اسم العميل'),
            ExportColumn::make('order.total')
                ->label('اجمالي الطلب')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('order.netTotal')
                ->label('صافي اجمالي الطلب')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('status')
                ->label('الحالة')
                ->state(fn(DriverTask $record): string => $record->status->getLabel()),
            ExportColumn::make('assismentOfficer.name')
                ->label('تم التعيين بواسطة'),
            ExportColumn::make('created_at')
                ->label('تاريخ التعيين')
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير طلبيات السائق وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
