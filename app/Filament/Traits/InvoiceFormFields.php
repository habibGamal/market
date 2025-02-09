<?php

namespace App\Filament\Traits;
use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Forms\Get;

trait InvoiceFormFields
{
    abstract protected static function invoiceTotal(Get $get): float;

    abstract protected static function handleProductsSelection(Set $set, Get $get, $products);

    public static function invoiceHeader()
    {
        return [
            Placeholder::make('officer')->label('المسؤول')->content(auth()->user()->name),
            Placeholder::make('total')->label('المجموع')->content(function (Get $get): string {
                return number_format(self::invoiceTotal($get), 2) . ' EGP';
            })
        ];
    }

    public static function productSelectSearch()
    {
        return Select::make('product_id')
            ->hiddenLabel()
            ->searchable(['name', 'barcode'])
            ->getSearchResultsUsing(fn(string $search): array => Product::where('name', 'like', "%$search%")->orWhere('barcode', 'like', "%$search%")->limit(10)->get()->pluck('name', 'id')->toArray())
            ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->name)
            ->searchDebounce(200)
            // ->options(Product::all()->pluck('name', 'id')->toArray())
            ->columnSpan(4)
            ->reactive()
            ->afterStateUpdated(
                function (Set $set, Get $get, ?int $state): void {
                    if ($state) {
                        $product = Product::find($state);
                        self::handleProductsSelection($set, $get, collect([$product]));
                    }
                }
            )
            ->autofocus();
    }

}
