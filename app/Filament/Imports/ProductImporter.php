<?php

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')->label('معرف المنتج'),
            ImportColumn::make('name')->label('اسم المنتج'),
            ImportColumn::make('barcode')->label('الباركود')
                ->rules(fn($record) => [
                    "unique:products,barcode,{$record?->id}"
                ]),
            ImportColumn::make('description')->label('الوصف'),
            ImportColumn::make('image')->label('الصورة'),
            ImportColumn::make('is_active')->label('نشط')
                ->boolean(),
            ImportColumn::make('can_sell_pieces')->label('بيع بالقطعة')
                ->boolean(),
            ImportColumn::make('packet_to_piece')->label('عدد القطع في العبوة'),
            ImportColumn::make('packet_alter_name')->label('الاسم البديل للعبوة'),
            ImportColumn::make('piece_alter_name')->label('الاسم البديل للقطعة'),
            ImportColumn::make('packet_cost')->label('تكلفة العبوة'),
            ImportColumn::make('packet_price')->label('سعر العبوة'),
            ImportColumn::make('piece_price')->label('سعر القطعة'),
            ImportColumn::make('before_discount_packet_price')->label('سعر العبوة قبل الخصم')
                ->guess(['سعر العبوة قبل الخصم'])
                ->requiredMappingForNewRecordsOnly(),
            ImportColumn::make('before_discount_piece_price')->label('سعر القطعة قبل الخصم')
                ->guess(['سعر القطعة قبل الخصم'])
                ->requiredMappingForNewRecordsOnly(),
            ImportColumn::make('expiration')->label('مدة الصلاحية'),
            ImportColumn::make('min_packets_stock_limit')->label('الحد الأدنى للمخزون (عبوات)'),
            ImportColumn::make('brand')->label('العلامة التجارية')
                ->relationship(resolveUsing: 'name'),
            ImportColumn::make('category')->label('الفئة')
                ->relationship(resolveUsing: 'name'),
        ];
    }

    public function afterFill(): void
    {
        $this->record->before_discount = $this->data['before_discount'] ?? $this->record->before_discount;

        if (!empty($this->data['image']) && str_starts_with($this->data['image'], 'https')) {
            $savePath = fetchAndSaveImageFromUrl(
                $this->data['image'],
                $this->data['barcode'] ?? time(),
                'products'
            );

            if ($savePath) {
                $this->record->image = $savePath;
            }
        }
    }

    public function resolveRecord(): ?Product
    {
        // Handle before_discount fields first
        if (isset($this->data['before_discount_packet_price']) || isset($this->data['before_discount_piece_price'])) {
            // Extract before_discount fields
            $beforeDiscountFields = array_filter($this->data, function ($key) {
                return strpos($key, 'before_discount_') === 0;
            }, ARRAY_FILTER_USE_KEY);

            // Initialize before_discount array
            $this->data['before_discount'] = [];

            // Process each before_discount field
            foreach ($beforeDiscountFields as $key => $value) {
                $fieldName = str_replace('before_discount_', '', $key);
                $this->data['before_discount'][$fieldName] = $value;

                // Remove the flattened field from the data array
                unset($this->data[$key]);
            }
        }

        // Look up existing record or create new one
        if (array_key_exists('id', $this->data) && $this->data['id']) {
            $product = Product::where('id', $this->data['id'])->first();

            if ($product) {
                return $product;
            }
        }

        return new Product();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد المنتجات بنجاح وتم استيراد ' . number_format($import->successful_rows) . ' ' . str('صف')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('صف')->plural($failedRowsCount) . ' فشل في الاستيراد.';
        }

        return $body;
    }

    // public function getJobRetryUntil(): ?CarbonInterface
    // {
    //     return now();
    // }
}
