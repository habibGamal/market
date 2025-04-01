<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CartItemsByCustomersReportExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('phone')
                ->label('رقم الهاتف'),
            ExportColumn::make('gov.name')
                ->label('المحافظة'),
            ExportColumn::make('city.name')
                ->label('المدينة'),
            ExportColumn::make('area.name')
                ->label('المنطقة'),
            ExportColumn::make('cart_items_count')
                ->label('عدد العناصر في السلة')
                ->counts('cartItems'),
            ExportColumn::make('cart.total')
                ->label('مجموع السلة')
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' EGP' : '0.00 EGP'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير تقرير سلة المشتريات حسب العملاء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
