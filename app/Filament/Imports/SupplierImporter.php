<?php

namespace App\Filament\Imports;

use App\Models\Supplier;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SupplierImporter extends Importer
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('الاسم')
                ->rules(['required', 'string']),

            ImportColumn::make('phone')
                ->label('رقم الهاتف')
                ->rules(['required', 'string', 'size:11', 'unique:suppliers,phone']),

            ImportColumn::make('company_name')
                ->label('اسم الشركة')
                ->rules(['required', 'string']),
        ];
    }

    public function resolveRecord(): ?Supplier
    {
        // Try to find by phone (unique identifier) or create new
        return Supplier::firstOrNew(['phone' => $this->data['phone']]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد الموردين بنجاح وتم استيراد ' . number_format($import->successful_rows) . ' ' . str('صف')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في الاستيراد.';
        }

        return $body;
    }
}
