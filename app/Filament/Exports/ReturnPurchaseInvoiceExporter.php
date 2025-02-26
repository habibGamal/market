<?php

namespace App\Filament\Exports;

use App\Filament\Resources\ReturnPurchaseInvoiceResource;
use App\Models\ReturnPurchaseInvoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ReturnPurchaseInvoiceExporter extends Exporter
{
    protected static ?string $model = ReturnPurchaseInvoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('رقم الفاتورة'),
            ExportColumn::make('supplier.name')
                ->label('المورد'),
            ExportColumn::make('total')
                ->label('المجموع'),
            ExportColumn::make('status')
                ->label('الحالة'),
            ExportColumn::make('officer.name')
                ->label('المسؤول'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'تم تصدير فواتير مرتجع المشتريات بنجاح';
    }

    protected function getTableQuery(): Builder
    {
        return static::$model::query()
            ->with(['supplier:id,name', 'officer:id,name']);
    }
}
