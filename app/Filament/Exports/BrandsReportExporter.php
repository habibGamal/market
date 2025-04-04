<?php

namespace App\Filament\Exports;

use App\Models\Brand;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BrandsReportExporter extends Exporter
{
    protected static ?string $model = Brand::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('order_items_sum_piece_quantity')
                ->label('كمية المبيعات'),
            ExportColumn::make('return_order_items_sum_piece_quantity')
                ->label('كمية المرتجعات'),
            ExportColumn::make('order_items_sum_total')
                ->label('قيمة المبيعات')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
            ExportColumn::make('order_items_sum_profit')
                ->label('الارباح')
                ->formatStateUsing(function ($state) {
                    if (!auth()->user()->can('view_profits_brand')) {
                        return '*** EGP';
                    }

                    return $state ? number_format($state, 2) . ' EGP' : '0.00 EGP';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير العلامات التجارية وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
