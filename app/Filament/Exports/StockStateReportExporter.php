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
                ->label('كمية المتاح'),
            ExportColumn::make('available_stock_cost')
                ->label('تكلفة المتاح'),
            ExportColumn::make('returned_stock')
                ->label('كمية المرتجع من المشتريات'),
            ExportColumn::make('returned_stock_cost')
                ->label('تكلفة المرتجع من المشتريات'),
            ExportColumn::make('waste_stock')
                ->label('كمية الهالك'),
            ExportColumn::make('waste_stock_cost')
                ->label('تكلفة الهالك'),
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

    protected function getTableQuery(): Builder
    {
        return app(StockStateReportService::class)->getProductsWithStockInfo();
    }
}
