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
                ->label('المنتج'),
            ExportColumn::make('brand.name')
                ->label('العلامة التجارية'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
            ExportColumn::make('sold_quantity')
                ->label('الكمية المباعة'),
            ExportColumn::make('total_sales')
                ->label('إجمالي المبيعات'),
            ExportColumn::make('total_profit')
                ->label('إجمالي الأرباح'),
            ExportColumn::make('returned_quantity')
                ->label('الكمية المرتجعة'),
            ExportColumn::make('total_returns')
                ->label('إجمالي المرتجعات'),
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

    protected function getTableQuery(): Builder
    {
        // Get the filtered query from the service, passing empty data to get all records
        return app(CartItemsByProductsReportService::class)->getFilteredQuery(parent::getTableQuery(), []);
    }
}
