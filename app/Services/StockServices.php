<?php

namespace App\Services;

use App\Models\Product;
use DB;

class StockServices
{
    /**
     * Add quantities to the stock of a product
     * @param \App\Models\Product $product
     * @param array $quantities
     * ex: $quantities = [
     *  '2025-02-12' => 10,
     *  '2025-02-13' => 20,
     * ]
     * @return void
     */
    public function addTo(Product $product, array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            // get the existing stock items with the release date in the array keys
            $existingStockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();
            // increment the quantities of the existing stock items
            $existingStockItems->each(function ($stockItem) use ($quantities) {
                $stockItem->increment('piece_quantity', $quantities[$stockItem->release_date]);
            });
            // get the release dates that are not in the existing stock items
            $newReleaseDates = array_diff(array_keys($quantities), $existingStockItems->pluck('release_date')->toArray());
            // create new stock items for the release dates that are not in the existing stock items
            $newStockItems = collect($newReleaseDates)->map(function ($releaseDate) use ($product, $quantities) {
                return [
                    'warehouse_id' => 1,
                    'product_id' => $product->id,
                    'piece_quantity' => $quantities[$releaseDate],
                    'unavailable_quantity' => 0,
                    'reserved_quantity' => 0,
                    'release_date' => $releaseDate,
                ];
            });
            $product->stockItems()->createMany($newStockItems);
        });
    }

    public function reserve(Product $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {
            $remainingQuantity = $quantity;

            // Get stock items ordered by release_date (FIFO)
            $stockItems = $product->stockItems()->where('piece_quantity', '>', 0)->orderBy('release_date')->lockForUpdate()->get();

            foreach ($stockItems as $stockItem) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $availableQuantity = $stockItem->piece_quantity - $stockItem->reserved_quantity - $stockItem->unavailable_quantity;

                if ($availableQuantity > 0) {
                    $reserveQuantity = min($availableQuantity, $remainingQuantity);
                    $stockItem->increment('reserved_quantity', $reserveQuantity);
                    $remainingQuantity -= $reserveQuantity;
                }
            }

            if ($remainingQuantity > 0) {
                throw new \Exception('الكمية المطلوبة غير متوفرة');
            }
        });
    }

    /**
     * Mark quantities as unavailable
     * @param \App\Models\Product $product
     * @param array $quantities
     * ex: $quantities = [
     *  '2025-02-12' => 10,
     *  '2025-02-13' => 20,
     * ]
     * @return void
     */
    public function unavailable(Product $product, array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            $stockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();

            $stockItems->each(function ($stockItem) use ($quantities) {

                $availableQuantity = $stockItem->piece_quantity - $stockItem->reserved_quantity - $stockItem->unavailable_quantity;
                if ($availableQuantity > $quantities[$stockItem->release_date]) {
                    $stockItem->increment('unavailable_quantity', $quantities[$stockItem->release_date]);
                } else {
                    throw new \Exception('الكمية المتاحة من المحتمل انه تم حجز الكمية للعملاء');
                }
            });
        });
    }

    /**
     * Undo the reservation of quantities
     * @param \App\Models\Product $product
     * @param int $quantity
     * @return void
     */
    public function undoReserve(Product $product, int $quantity)
    {
        DB::transaction(function () use ($product, $quantity) {
            $remainingQuantity = $quantity;

            // Get stock items ordered by release_date (FIFO)
            $stockItems = $product->stockItems()->where('reserved_quantity', '>', 0)->orderBy('release_date')->lockForUpdate()->get();

            foreach ($stockItems as $stockItem) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $reservedQuantity = $stockItem->reserved_quantity;

                if ($reservedQuantity > 0) {
                    $removeQuantity = min($reservedQuantity, $remainingQuantity);
                    $stockItem->decrement('reserved_quantity', $removeQuantity);
                    $remainingQuantity -= $removeQuantity;
                }
            }

            if ($remainingQuantity > 0) {
                throw new \Exception('الكمية المطلوبة اكبر من الكمية المحجوزة');
            }
        });
    }

    /**
     * @param \App\Models\Product $product
     * @param array $quantities
     * ex: $quantities = [
     *  '2025-02-12' => 10,
     *  '2025-02-13' => 20,
     * ]
     * @return void
     */
    public function removeFromReserve(Product $product,array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            $stockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();

            $stockItems->each(function ($stockItem) use ($quantities) {
                $stockItem->decrement('piece_quantity', $quantities[$stockItem->release_date]);
                $stockItem->decrement('reserved_quantity', $quantities[$stockItem->release_date]);
            });
        });
    }

    /**
     * @param \App\Models\Product $product
     * @param array $quantities
     * ex: $quantities = [
     *  '2025-02-12' => 10,
     *  '2025-02-13' => 20,
     * ]
     * @return void
     */
    public function removeFromUnavailable(Product $product,array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            $stockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();

            $stockItems->each(function ($stockItem) use ($quantities) {
                $stockItem->decrement('piece_quantity', $quantities[$stockItem->release_date]);
                $stockItem->decrement('unavailable_quantity', $quantities[$stockItem->release_date]);
            });
        });
    }
}
