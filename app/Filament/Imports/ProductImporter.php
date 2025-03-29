<?php

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')->label('معرف'),

            ImportColumn::make('name')->label('الاسم'),
            ImportColumn::make('barcode')->label('الباركود')->rules(['unique:products,barcode,{{record.id}}']),
            ImportColumn::make('packet_to_piece')->label('عدد القطع في العبوة'),
            ImportColumn::make('packet_cost')->label('تكلفة العبوة'),
            ImportColumn::make('packet_price')->label('سعر العبوة'),
            ImportColumn::make('piece_price')->label('سعر القطعة'),
            ImportColumn::make('before_discount.packet_price')->label('سعر العبوة قبل الخصم')
                ->fillRecordUsing(function (Product $product, string $state) {
                    $product->before_discount = array_merge($product->before_discount ?? [], ['packet_price' => $state]);
                }),
            ImportColumn::make('before_discount.piece_price')->label('سعر القطعة قبل الخصم')
                ->fillRecordUsing(function (Product $product, string $state) {
                    $product->before_discount = array_merge($product->before_discount ?? [], ['piece_price' => $state]);
                }),

            ImportColumn::make('expiration')->label('مدة الصلاحية'),

            ImportColumn::make('brand')->label('العلامة التجارية')
                ->relationship(),
            ImportColumn::make('category')->label('الفئة')
                ->relationship()
            ,
        ];
    }

    public function resolveRecord(): ?Product
    {
        if (array_key_exists('id', $this->data)) {
            $product = Product::where('id', $this->data['id'])->first();

            if ($product) {
                $product->fill($this->data);
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
