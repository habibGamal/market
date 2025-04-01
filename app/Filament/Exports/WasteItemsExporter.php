<?php

namespace App\Filament\Exports;

use App\Models\WasteItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class WasteItemsExporter extends Exporter
{
    protected static ?string $model = WasteItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('waste.id')
                ->label('رقم إذن الهدر'),
            ExportColumn::make('product.name')
                ->label('المنتج'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات'),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('piece_quantity')
                ->label('عدد القطع'),
            ExportColumn::make('total')
                ->label('الإجمالي')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('release_date')
                ->label('تاريخ الإنتاج'),
            ExportColumn::make('waste.officer.name')
                ->label('المسؤول'),
            ExportColumn::make('waste.created_at')
                ->label('تاريخ الانشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير هدر المنتج وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
