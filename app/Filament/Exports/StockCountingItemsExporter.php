<?php

namespace App\Filament\Exports;

use App\Models\StockCountingItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockCountingItemsExporter extends Exporter
{
    protected static ?string $model = StockCountingItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('stockCounting.id')
                ->label('رقم إذن الجرد'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('old_packets_quantity')
                ->label('عدد العبوات (قديم)'),
            ExportColumn::make('old_piece_quantity')
                ->label('عدد القطع (قديم)'),
            ExportColumn::make('new_packets_quantity')
                ->label('عدد العبوات (جديد)'),
            ExportColumn::make('new_piece_quantity')
                ->label('عدد القطع (جديد)'),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('total_diff')
                ->label('الفرق')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('release_date')
                ->label('تاريخ الإنتاج'),
            ExportColumn::make('stockCounting.officer.name')
                ->label('المسؤول'),
            ExportColumn::make('stockCounting.created_at')
                ->label('تاريخ الانشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير جرد المنتج وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
