<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrdersReportExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الطلبية'),
            ExportColumn::make('customer.name')
                ->label('العميل'),
            ExportColumn::make('customer.area.name')
                ->label('المنطقة'),
            ExportColumn::make('driver.name')
                ->label('السائق'),
            ExportColumn::make('profit')
                ->label('الربح'),
            ExportColumn::make('net_profit')
                ->label('صافي الربح'),
            ExportColumn::make('total')
                ->label('المجموع'),
            ExportColumn::make('status')
                ->label('الحالة')
                ->state(fn(Order $order) => $order->status->getLabel()),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير الطلبات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
