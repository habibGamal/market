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
    protected static $brands = null;

    abstract protected static function handleProductsSelection(Set $set, Get $get, $products);

    public static function importProductsByBrandAction()
    {
        return Actions\Action::make('select_products')
            ->label('إضافة مجموعة من المنتجات')
            ->modal()
            ->form(
                function () {
                    static::$brands ??= Brand::with('products')->get();
                    return [
                        Section::make('products')
                            ->columns(4)
                            ->schema(
                                static::$brands->mapWithKeys(function ($brand) {
                                    return [$brand->name => $brand->products->pluck('name', 'id')->toArray()];
                                })->map(function ($products, $brand) {
                                    return Section::make($brand)
                                        ->schema([
                                            CheckboxList::make($brand)
                                                ->options($products)
                                                ->bulkToggleable()
                                        ])
                                        ->collapsed()
                                        ->columnSpan(1);
                                })->toArray()
                            )
                    ];
                }
            )
            ->action(function (array $data, Get $get, Set $set): void {
                $product_ids = array_merge(...array_values($data));
                $products = Product::find($product_ids);
                static::handleProductsSelection($set, $get, $products);
            })
            ->modalSubmitActionLabel('إضافة المنتجات');
    }

    abstract protected static function csvTitles(): array;


    abstract protected static function itemKeysAliases(): array;

    public static function exportCSVAction()
    {
        return Actions\Action::make('exportItems')
            ->label('تصدير العناصر')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (Get $get) {
                $items = collect($get('items'));
                $csvService = app(InvoiceItemsCSVService::class);
                $csv = $csvService->export(items: $items, titles: array_values(static::csvTitles()), mapperCallback: function ($item) {
                    return [
                        $item['product_id'],
                        $item['product_name'],
                        $item[static::itemKeysAliases()['quantity']],
                        $item[static::itemKeysAliases()['price']],
                        $item['total'],
                    ];
                });
                return static::download($csv);
            });
    }

    public static function importCSVAction()
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
            ->action(function (array $data, Get $get, Set $set) {
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

                    // Process CSV records
                    $records = $records->map(function ($record) {
                        return [
                            'product_id' => $record[static::csvTitles()['product_id']],
                            'product_name' => Product::find($record[static::csvTitles()['product_id']])->name ?? null,
                            static::itemKeysAliases()['quantity'] => (float) $record[static::csvTitles()['quantity']],
                            static::itemKeysAliases()['price'] => (float) $record[static::csvTitles()['price']],
                            'total' => (float) $record[static::csvTitles()['quantity']] * (float) $record[static::csvTitles()['price']],
                        ];
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
