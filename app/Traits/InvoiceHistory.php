<?php

namespace App\Traits;

use App\Models\Product;

trait InvoiceHistory
{
    protected $oldItems = [];

    public function setOldItems($items)
    {
        $this->oldItems = $items;
    }

    public function compareItems($keys)
    {
        $mergedItems = collect($this->oldItems)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item];
        });

        foreach ($this->items as $item) {
            $productId = $item->product_id;
            $oldItem = $mergedItems->get($productId, array_merge(['product' => $item->product->name], array_fill_keys($keys, "0")));

            $mergedItems->put($productId, array_merge(['product' => $item->product->name], array_combine($keys, array_map(function ($key) use ($oldItem, $item) {
                return "{$oldItem[$key]} -> {$item->$key}";
            }, $keys))));
        }

        foreach ($this->oldItems as $oldItem) {
            if (!$this->items->contains('product_id', $oldItem['product_id'])) {
                $mergedItems->put(
                    $oldItem['product_id'],
                    array_merge(
                        ['product' => Product::select(['id', 'name'])->find($oldItem['product_id'])->name]
                        ,
                        array_combine($keys, array_map(
                            function ($key) use ($oldItem) {
                                return "{$oldItem[$key]} -> 0";
                            },
                            $keys
                        ))
                    )
                );
            }
        }
        // dd($this->oldItems, $mergedItems);
        return $mergedItems->toArray();
    }
}
