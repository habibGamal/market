<?php
namespace App\Filament\Exports;

use App\Filament\Resources\WasteResource;
use App\Models\Waste;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class WasteExporter extends Exporter
{
    protected static ?string $model = Waste::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الإذن'),
            ExportColumn::make('total')
                ->label('المجموع'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('officer.name')
                ->label('المسؤول'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'تم تصدير سجلات التوالف بنجاح';
    }
}
