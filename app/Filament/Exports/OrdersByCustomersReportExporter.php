<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use App\Services\Reports\OrdersByCustomersReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrdersByCustomersReportExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('العميل'),
            ExportColumn::make('area.name')
                ->label('المنطقة'),
            ExportColumn::make('orders_count')
                ->label('عدد الطلبات'),
            ExportColumn::make('total_sales')
                ->label('قيمة المبيعات'),
            ExportColumn::make('total_profit')
                ->label('صافي الأرباح'),
            ExportColumn::make('total_returns')
                ->label('قيمة المرتجعات'),
            ExportColumn::make('total_cancelled')
                ->label('قيمة الملغية'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير الطلبات حسب العملاء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
