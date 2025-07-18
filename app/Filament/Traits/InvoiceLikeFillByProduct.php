<?php

namespace App\Filament\Traits;

use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\Product;
use App\Models\Brand;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;

trait InvoiceLikeFillByProduct
{

    protected static $brands = null;

    /**
     * Handle the selection of products and update the invoice items.
     *
     * @param Set $set
     * @param Get $get
     * @param \Illuminate\Support\Collection $products
     * @return void
     */
    protected static function handleProductsSelection(Set $set, Get $get, $products, callable $updateExistingItem, callable $newItem): void
    {
        $items = [...$get('items')];
        // in case of starting with empty items
        if (array_key_exists(0, array_keys($items)) && $items[array_keys($items)[0]]['product_id'] == null)
            $items = [];

        // get the existing items & new added products
        $items = collect($items);
        $existing_products_ids = $items->whereIn('product_id', $products->pluck('id'))->pluck('product_id')->toArray();
        $new_products = $products->whereNotIn('id', $existing_products_ids);
        $items = $items->toArray();

        // in case of the product is already in the items increment the quantity by 1
        foreach ($items as &$item) {
            if (in_array($item['product_id'], $existing_products_ids)) {
                $item = $updateExistingItem($item);
            }
        }

        // add the new products to the items
        $new_products = $new_products->map(function ($product) use ($newItem) {
            return $newItem($product);
        })->toArray();

        array_push($items, ...$new_products);

        // update the items & reset the field
        $set('items', $items);
        $set('product_id', null);
    }

    /**
     * Create a searchable select component for selecting products.
     *
     * @return Select
     */
    public static function productSelectSearch(
        callable $updateExistingItem,
        callable $newItem
    ) {
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
                function (Set $set, Get $get, ?int $state) use ($updateExistingItem, $newItem) {
                    if ($state) {
                        $product = Product::select(['id', 'name', 'packet_cost', 'packet_price', 'packet_to_piece'])->find($state);
                        self::handleProductsSelection($set, $get, collect([$product]), $updateExistingItem, $newItem);
                    }
                }
            )
            ->autofocus();
    }

    public static function importProductsByBrandAction(
        callable $updateExistingItem,
        callable $newItem
    ) {
        return Actions\Action::make('select_products')
            ->label('إضافة مجموعة من المنتجات')
            ->modal()
            ->form(
                function () {
                    static::$brands ??= Brand::with('products:id,name,brand_id')->get();
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
            ->action(function (array $data, Get $get, Set $set) use ($updateExistingItem, $newItem) {
                $product_ids = array_merge(...array_values($data));
                $products = Product::select(['id', 'name', 'packet_cost', 'packet_price', 'packet_to_piece'])->find($product_ids);
                static::handleProductsSelection($set, $get, $products, $updateExistingItem, $newItem);
            })
            ->modalSubmitActionLabel('إضافة المنتجات');
    }
}
