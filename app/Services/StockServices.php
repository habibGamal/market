<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockItem;
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
            $data = collect($quantities)->map(function ($quantity, $releaseDate) use ($product) {
                return [
                    'warehouse_id' => 1,
                    'product_id' => $product->id,
                    'piece_quantity' => $quantity,
                    'unavailable_quantity' => 0,
                    'reserved_quantity' => 0,
                    'release_date' => $releaseDate,
                ];
            })->toArray();

            StockItem::upsert($data, ['product_id', 'release_date'], ['piece_quantity' => DB::raw('piece_quantity + VALUES(piece_quantity)'), 'reserved_quantity' => DB::raw('reserved_quantity + VALUES(reserved_quantity)'), 'unavailable_quantity' => DB::raw('unavailable_quantity + VALUES(unavailable_quantity)')]);
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
     * Get quantities to be reserved from stock items using FIFO
     * @param \App\Models\Product $product
     * @param int $quantity
     * @return array
     */
    public function getReservedQuantities(Product $product, int $quantity): array
    {
        $quantities = [];
        $remainingQuantity = $quantity;

        // Get stock items ordered by release_date (FIFO)
        $stockItems = $product->stockItems()
            ->where('piece_quantity', '>', 0)
            ->orderBy('release_date')
            ->get();

        foreach ($stockItems as $stockItem) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableQuantity = $stockItem->reserved_quantity;

            if ($availableQuantity > 0) {
                $reserveQuantity = min($availableQuantity, $remainingQuantity);
                $quantities[$stockItem->release_date] = $reserveQuantity;
                $remainingQuantity -= $reserveQuantity;
            }
        }

        if ($remainingQuantity > 0) {
            throw new \Exception('الكمية المطلوبة غير متوفرة');
        }

        return $quantities;
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
                if ($availableQuantity >= $quantities[$stockItem->release_date]) {
                    $stockItem->increment('unavailable_quantity', $quantities[$stockItem->release_date]);
                } else {
                    throw new \Exception("الكمية المتاحة للمنتج {$stockItem->product->name} بتاريخ {$stockItem->release_date} هي {$availableQuantity} من المحتمل انه تم حجز الكمية للعملاء");
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
    public function removeFromReserve(Product $product, array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            $stockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();

            $stockItems->each(function ($stockItem) use ($quantities) {
                if ($stockItem->reserved_quantity < $quantities[$stockItem->release_date]) {
                    throw new \Exception('الكمية المحجوزة غير كافية');
                }
                if ($stockItem->piece_quantity < $quantities[$stockItem->release_date]) {
                    throw new \Exception('الكمية المتاحة غير كافية');
                }
                $stockItem->decrement('piece_quantity', $quantities[$stockItem->release_date]);
                $stockItem->decrement('reserved_quantity', $quantities[$stockItem->release_date]);
                if ($stockItem->piece_quantity === 0) {
                    $stockItem->delete();
                }
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
    public function removeFromUnavailable(Product $product, array $quantities)
    {
        DB::transaction(function () use ($product, $quantities) {
            $stockItems = $product->stockItems()->whereIn('release_date', array_keys($quantities))->lockForUpdate()->get();

            $stockItems->each(function ($stockItem) use ($quantities) {
                if ($stockItem->unavailable_quantity < $quantities[$stockItem->release_date]) {
                    throw new \Exception('الكمية المتاحة غير كافية');
                }
                if ($stockItem->piece_quantity < $quantities[$stockItem->release_date]) {
                    throw new \Exception('الكمية المتاحة غير كافية');
                }
                $stockItem->decrement('piece_quantity', $quantities[$stockItem->release_date]);
                $stockItem->decrement('unavailable_quantity', $quantities[$stockItem->release_date]);
                if ($stockItem->piece_quantity === 0) {
                    $stockItem->delete();
                }
            });
        });
    }
}
