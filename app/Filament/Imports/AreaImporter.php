<?php

namespace App\Filament\Imports;

use App\Models\Area;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AreaImporter extends Importer
{
    protected static ?string $model = Area::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->example('ابو تيج')
                ->exampleHeader('name')
                ->label('الاسم')
                ->rules(['required', 'max:255', 'unique:areas,name'])
            ,
        ];
    }

    public function resolveRecord(): ?Area
    {
        return Area::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم اكتمال استيراد المناطق وتم استيراد ' . number_format($import->successful_rows) . ' ' . str('صف')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في الاستيراد.';
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMinutes(1);
    }
}
