<?php

namespace App\Filament\Exports;

use App\Models\Area;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

class AreaExporter extends Exporter
{
    protected static ?string $model = Area::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('city.name')
                ->label('المدينة'),
            ExportColumn::make('city.gov.name')
                ->label('المحافظة'),
            ExportColumn::make('has_village')
                ->label('لديها قرى')
                ->state(fn (Area $record): string => $record->has_village ? 'نعم' : 'لا'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير المناطق وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . trans_choice('تعذر تصدير :count صف|تعذر تصدير :count صفوف', $failedRowsCount);
        }

        return $body;
    }
}
