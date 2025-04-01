<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductsReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('barcode')
                ->label('الباركود'),
            ExportColumn::make('brand.name')
                ->label('العلامة التجارية'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
            ExportColumn::make('order_items_sum_piece_quantity')
                ->label('كمية المبيعات')
                ->state(function (Product $record): string {
                    $packets = $record->order_items_sum_piece_quantity / $record->packet_to_piece;
                    return "{$record->order_items_sum_piece_quantity} قطعة = {$packets} عبوة";
                }),
            ExportColumn::make('return_order_items_sum_piece_quantity')
                ->label('كمية المرتجعات')
                ->state(function (Product $record): string {
                    $packets = $record->return_order_items_sum_piece_quantity / $record->packet_to_piece;
                    return "{$record->return_order_items_sum_piece_quantity} قطعة = {$packets} عبوة";
                }),
            ExportColumn::make('order_items_sum_total')
                ->label('قيمة المبيعات')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('order_items_sum_profit')
                ->label('ارباح المنتج')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('packet_cost')
                ->label('تكلفة العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('packet_price')
                ->label('سعر العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
            ExportColumn::make('piece_price')
                ->label('سعر القطعة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' EGP'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير المنتجات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
