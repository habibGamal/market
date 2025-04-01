<?php

namespace App\Filament\Exports;

use App\Models\OrderItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderItemsExporter extends Exporter
{
    protected static ?string $model = OrderItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order.id')
                ->label('رقم الطلب'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات'),
            ExportColumn::make('packet_price')
                ->label('سعر العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('piece_quantity')
                ->label('عدد القطع'),
            ExportColumn::make('piece_price')
                ->label('سعر القطعة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('total')
                ->label('الإجمالي')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('profit')
                ->label('الربح')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('order.status')
                ->label('حالة الطلب')
                ->state(fn (OrderItem $record): string => $record->order->status->getLabel()),
            ExportColumn::make('order.customer.name')
                ->label('اسم العميل'),
            ExportColumn::make('order.created_at')
                ->label('تاريخ الطلب'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير مبيعات المنتج وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
