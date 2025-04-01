<?php

namespace App\Filament\Exports;

use App\Models\Product;
use App\Services\Reports\CartItemsByProductsReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class CartItemsByProductsReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم المنتج'),
            ExportColumn::make('barcode')
                ->label('الباركود'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
            ExportColumn::make('packet_price')
                ->label('سعر العبوة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' جنيه'),
            ExportColumn::make('piece_price')
                ->label('سعر القطعة')
                ->formatStateUsing(fn ($state) => number_format($state, 2) . ' جنيه'),
            ExportColumn::make('cart_items_count')
                ->label('عدد مرات الإضافة للسلة')
                ->state(function (Product $record): int {
                    return $record->cartItems()->count();
                }),
            ExportColumn::make('cart_items_sum_packets_quantity')
                ->label('مجموع العبوات في السلات')
                ->state(function (Product $record): int {
                    return $record->cartItems()->sum('packets_quantity');
                }),
            ExportColumn::make('cart_items_sum_piece_quantity')
                ->label('مجموع القطع في السلات')
                ->state(function (Product $record): int {
                    return $record->cartItems()->sum('piece_quantity');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير المبيعات حسب المنتجات وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
