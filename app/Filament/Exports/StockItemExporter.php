<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockItemExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم المنتج'),
            ExportColumn::make('barcode')
                ->label('الباركود'),
            ExportColumn::make('name')
                ->label('اسم'),
            ExportColumn::make('stock_items_sum_piece_quantity')
                ->sum('stockItems', 'piece_quantity')
                ->label('عدد القطع'),
            ExportColumn::make('packets_quantity')
                ->label('عدد العبوات')
                ->formatStateUsing(fn($state) => number_format($state, 2)),
            ExportColumn::make('packets_and_pieces')
                ->label('العبوات والقطع')
                ->formatStateUsing(function ($record) {
                    $totalPieces = $record->stock_items_sum_piece_quantity ?? 0;
                    $packetToPiece = $record->packet_to_piece ?? 1;

                    $packets = floor($totalPieces / $packetToPiece);
                    $pieces = $totalPieces % $packetToPiece;

                    if ($packets > 0 && $pieces > 0) {
                        return "{$packets} عبوة و {$pieces} قطعة";
                    } elseif ($packets > 0) {
                        return "{$packets} عبوة";
                    } else {
                        return "{$pieces} قطعة";
                    }
                }),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة'),
            ExportColumn::make('packet_price')
                ->label('سعر العبوة'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير مستويات المخزن وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
