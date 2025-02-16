<?php

namespace App\Filament\Traits;
use App\Models\Brand;
use App\Models\Product;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use App\Services\InvoiceItemsCSVService;

trait InvoiceActions
{

    abstract public static function csvTitles(): array;


    public static function exportCSVAction(callable $mapColumns)
    {

        return Actions\Action::make('exportItems')
            ->label('تصدير العناصر')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (Get $get) use ($mapColumns) {
                $items = collect($get('items'));
                $products = Product::select(['name', 'id'])->whereIn('id', $items->pluck('product_id'))->get();
                $csvService = app(InvoiceItemsCSVService::class);
                $csv = $csvService->export(items: $items, titles: array_values(static::csvTitles()), mapperCallback: function ($item) use ($products, $mapColumns) {
                    $product = $products->where('id', $item['product_id'])->first();
                    return $mapColumns($item, $product);
                });
                return static::download($csv);
            });
    }

    public static function importCSVAction($mapColumns)
    {
        return Actions\Action::make('importItems')
            ->label('استيراد من ملف')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                FileUpload::make('csv_file')
                    ->label('ملف CSV')
                    ->acceptedFileTypes(['text/csv'])
                    ->required()
            ])
            ->action(function (array $data, Get $get, Set $set) use ($mapColumns) {
                try {
                    // Read uploaded file
                    $path = storage_path('app/public/' . $data['csv_file']);

                    $csvService = app(InvoiceItemsCSVService::class);

                    $records = $csvService->import($path);

                    // catch any duplicate product ids
                    $duplicate_product_ids = $records->pluck(static::csvTitles()['product_id'])->duplicates()->toArray();
                    if (count($duplicate_product_ids) > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('تكرار في البيانات')
                            ->body('تم العثور على تكرار في البيانات. أرقام المنتجات المكررة: ' . implode(', ', $duplicate_product_ids))
                            ->danger()
                            ->send();
                        return;
                    }
                    $products = Product::select(['name', 'id'])->whereIn('id', $records->pluck(static::csvTitles()['product_id']))->get();
                    // Process CSV records
                    $records = $records->map(function ($record) use ($products, $mapColumns) {
                        $product = $products->where('id', $record[static::csvTitles()['product_id']])->first();
                        return $mapColumns($record, $product);
                    })->filter(callback: function ($record) {
                        return $record['product_id'] !== null && $record['product_id'] !== '';
                    });

                    // Update repeater
                    $set('items', $records->toArray());

                    // Clean up uploaded file
                    unlink($path);


                } catch (\Exception $e) {
                    logger()->error('CSV Import Error: ' . $e->getMessage());

                    \Filament\Notifications\Notification::make()
                        ->title('حدث خطأ أثناء الاستيراد')
                        ->danger()
                        ->send();
                }
            });
    }



    /**
     * Download CSV file.
     *
     * @param \League\Csv\Writer $csv
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private static function download($csv)
    {
        $filename = sprintf(
            'invoice-items-%s.csv',
            now()->format('Y-m-d-H-i-s')
        );

        return response()
            ->streamDownload(function () use ($csv) {
                echo $csv->toString();
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
    }
}
