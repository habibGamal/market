<?php

namespace App\Filament\Exports;

use App\Models\Product;
use App\Services\Reports\StockStateReportService;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class StockStateReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('اسم المنتج'),
            ExportColumn::make('brand.name')
                ->label('العلامة التجارية'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
            ExportColumn::make('available_stock')
                ->label('كمية المتاح')
                ->state(function (Product $record): string {
                    $pieces = $record->available_stock;
                    $packets = $pieces / $record->packet_to_piece;
                    return "{$pieces} قطعة = {$packets} عبوة";
                }),
            ExportColumn::make('available_stock_cost')
                ->label('تكلفة المتاح')
                ->state(function (Product $record): string {
                    return number_format($record->available_stock_cost, 2) . ' EGP';
                }),
            ExportColumn::make('returned_stock')
                ->label('كمية المرتجع من المشتريات')
                ->state(function (Product $record): string {
                    $pieces = $record->returned_stock;
                    $packets = $pieces / $record->packet_to_piece;
                    return "{$pieces} قطعة = {$packets} عبوة";
                }),
            ExportColumn::make('returned_stock_cost')
                ->label('تكلفة المرتجع من المشتريات')
                ->state(function (Product $record): string {
                    return number_format($record->returned_stock_cost, 2) . ' EGP';
                }),
            ExportColumn::make('waste_stock')
                ->label('كمية الهالك')
                ->state(function (Product $record): string {
                    $pieces = $record->waste_stock;
                    $packets = $pieces / $record->packet_to_piece;
                    return "{$pieces} قطعة = {$packets} عبوة";
                }),
            ExportColumn::make('waste_stock_cost')
                ->label('تكلفة الهالك')
                ->state(function (Product $record): string {
                    return number_format($record->waste_stock_cost, 2) . ' EGP';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير حالة المخزون وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
