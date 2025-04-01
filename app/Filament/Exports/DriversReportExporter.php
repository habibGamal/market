<?php

namespace App\Filament\Exports;

use App\Models\Driver;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DriversReportExporter extends Exporter
{
    protected static ?string $model = Driver::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم مندوب التسليم'),
            ExportColumn::make('pending_orders_count')
                ->label('عدد الطلبات قيد التسليم'),
            ExportColumn::make('out_for_delivery_total')
                ->label('قيمة الطلبات قيد التسليم')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('total_returns')
                ->label('قيمة المرتجعات')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('account.balance')
                ->label('الرصيد')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير مندوبين التسليم وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
