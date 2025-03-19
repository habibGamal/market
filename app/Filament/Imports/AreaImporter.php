<?php

namespace App\Filament\Imports;

use App\Models\Area;
use App\Models\City;
use App\Models\Gov;
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
                ->example('الحي الأول')
                ->label('الاسم')
                ->rules(['required', 'max:255'])
                ->requiredMapping(),
            ImportColumn::make('city')
                ->example('مدينة نصر')
                ->label('المدينة')
                ->rules(['required'])
                ->requiredMapping(),
            ImportColumn::make('gov')
                ->example('القاهرة')
                ->label('المحافظة')
                ->rules(['required'])
                ->requiredMapping(),
            ImportColumn::make('has_village')
                ->example('نعم')
                ->label('لديها قرى')
                ->rules(['boolean'])
                ->boolean()
                ->trueValue('نعم')
                ->falseValue('لا'),
        ];
    }

    public function resolveRecord(): ?Area
    {
        $gov = Gov::firstOrCreate([
            'name' => $this->data['gov'],
        ]);

        $city = City::firstOrCreate([
            'name' => $this->data['city'],
            'gov_id' => $gov->id,
        ]);

        return Area::firstOrCreate([
            'name' => $this->data['name'],
            'city_id' => $city->id,
        ], [
            'has_village' => $this->data['has_village'] ?? false,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد المناطق بنجاح وتم استيراد ' . number_format($import->successful_rows) . ' ' . str('صف')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . trans_choice('تعذر استيراد :count صف|تعذر استيراد :count صفوف', $failedRowsCount);
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMinutes(1);
    }
}
