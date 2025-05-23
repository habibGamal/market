<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('الاسم')
                ->rules(['required', 'string', 'unique:categories,name']),
            ImportColumn::make('image')
                ->label('الصورة'),
        ];
    }

    public function resolveRecord(): ?Category
    {
        return Category::firstOrNew(['name' => $this->data['name']]);
    }

    public function afterFill(): void
    {
        if (!empty($this->data['image']) && str_starts_with($this->data['image'], 'https')) {
            $savePath = fetchAndSaveImageFromUrl(
                $this->data['image'],
                $this->data['name'],
                'category-images'
            );

            if ($savePath) {
                $this->record->image = $savePath;
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد الفئات بنجاح وتم استيراد ' . number_format($import->successful_rows) . ' ' . str('صف')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في الاستيراد.';
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMilliseconds(20000);
    }
}
