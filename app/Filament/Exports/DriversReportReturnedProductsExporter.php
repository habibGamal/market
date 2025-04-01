<?php

namespace App\Filament\Exports;

use App\Models\Driver;
use App\Models\DriverReturnedProduct;
use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class DriversReportReturnedProductsExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم المنتج'),
            ExportColumn::make('name')
                ->label('اسم المنتج'),
            ExportColumn::make('packets_quantity')
                ->state(
                    fn($record) =>$record->pivot_packets_quantity,
                )
                ->label('عدد العبوات'),
            ExportColumn::make('piece_quantity')
                ->label('عدد القطع'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير المنتجات المرتجعة وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }


    // public static function modifyQuery(Builder $query): Builder
    // {
    //     dd($query->toSql());
    //     return $query;
    // }
}
