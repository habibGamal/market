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
                ->label('الحالة'),
            ExportColumn::make('officer.name')
                ->label('المسؤول'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'تم تصدير سجلات جرد المخزون بنجاح';
    }

    protected function getTableQuery(): Builder
    {
        return static::$model::query()
            ->with(['officer:id,name']);
    }
}
