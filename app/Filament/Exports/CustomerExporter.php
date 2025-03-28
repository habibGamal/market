<?php
namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('معرف العميل'),
            ExportColumn::make('name')->label('الاسم'),
            ExportColumn::make('phone')->label('رقم الهاتف'),
            ExportColumn::make('whatsapp')->label('رقم الواتساب'),
            ExportColumn::make('email')->label('البريد الإلكتروني'),
            ExportColumn::make('gov.name')->label('المحافظة'),
            ExportColumn::make('city.name')->label('المدينة'),
            ExportColumn::make('area.name')->label('المنطقة'),
            ExportColumn::make('location')->label('الموقع'),
            ExportColumn::make('village')->label('القرية'),
            ExportColumn::make('address')->label('العنوان التفصيلي'),
            ExportColumn::make('rating_points')->label('نقاط التقييم'),
            ExportColumn::make('blocked')->label('محظور'),
            ExportColumn::make('created_at')->label('تاريخ التسجيل'),
            ExportColumn::make('updated_at')->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم اكتمال تصدير العملاء وتم تصدير ' . number_format($export->successful_rows) . ' ' . str('صف')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في التصدير.';
        }

        return $body;
    }
}
