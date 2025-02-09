<?php

namespace App\Filament\Interfaces;

use App\Filament\Traits\InvoiceActions;
use App\Filament\Traits\InvoiceFormFields;
use Filament\Resources\Resource;
use Filament\Forms\Set;
use Filament\Forms\Get;

abstract class InvoiceResource extends Resource
{
    use InvoiceActions;
    use InvoiceFormFields;

    protected static function invoiceTotal(Get $get): float
    {
        $items = $get('items');
        return array_reduce($items, function ($carry, $item) {
            return $carry + $item['total'];
        }, 0);
    }

     /**
     * /
     * @param \Filament\Forms\Set $set
     * @param \Filament\Forms\Get $get
     * @param \Illuminate\Support\Collection $products
     * @return void
     */
    protected static function handleProductsSelection(Set $set, Get $get, $products): void
    {
        $items = [...$get('items')];
        // in case of starting with empty items
        if ($items[array_keys($items)[0]]['product_id'] == null)
            $items = [];

        // get the existing items & new added products
        $items = collect($items);
        $existing_products_ids = $items->whereIn('product_id', $products->pluck('id'))->pluck('product_id')->toArray();
        $new_products = $products->whereNotIn('id', $existing_products_ids);
        $items = $items->toArray();

        // in case of the product is already in the items increment the quantity by 1
        foreach ($items as &$item) {
            if (in_array($item['product_id'], $existing_products_ids)) {
                $item[static::itemKeysAliases()['quantity']] += 1;
                $item['total'] = $item[static::itemKeysAliases()['quantity']] * $item[static::itemKeysAliases()['price']];
            }
        }

        // add the new products to the items
        $new_products = $new_products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                static::itemKeysAliases()['quantity'] => 1,
                static::itemKeysAliases()['price'] => $product->packet_cost,
                'total' => $product->packet_cost * 1,
            ];
        })->toArray();

        array_push($items, ...$new_products);

        // update the items & reset the field
        $set('items', $items);
        $set('product_id', null);
    }

}
