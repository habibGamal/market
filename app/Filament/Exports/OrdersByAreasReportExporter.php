<?php

namespace App\Filament\Exports;

use App\Models\Area;
use App\Services\Reports\OrdersByAreasReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class OrdersByAreasReportExporter extends Exporter
{
    protected static ?string $model = Area::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('المنطقة'),
            ExportColumn::make('orders_count')
                ->label('عدد الطلبات'),
            ExportColumn::make('total_sales')
                ->label('قيمة المبيعات'),
            ExportColumn::make('total_profit')
                ->label('الأرباح')
                ->formatStateUsing(function ($state) {
                    if (!auth()->user()->can('view_profits_area', Area::class)) {
                        return '*** EGP';
                    }

                    return $state ? number_format($state, 2) . ' EGP' : '0.00 EGP';
                }),
            ExportColumn::make('total_returns')
                ->label('قيمة المرتجعات'),
            ExportColumn::make('total_cancelled')
                ->label('قيمة الملغية')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير الطلبات حسب المناطق وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
