<?php

namespace App\Services;

use App\Models\StockCounting;
use Illuminate\Support\Facades\DB;

class StockCountingServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {
    }

    /**
     * Process stock counting and adjust stock piece_quantity
     * Adds to stock if difference is positive, removes from stock if negative
     *
     * @param StockCounting $stockCounting
     * @return void
     * @throws \Exception If there's an issue with adjusting stock quantities
     */
    public function processStockCounting(StockCounting $stockCounting): void
    {
        DB::transaction(function () use ($stockCounting) {
            $productQuantities = $this->getQuantities($stockCounting);

            foreach ($productQuantities as $productData) {
                $product = $productData['product'];
                $positiveQuantities = $productData['positive_quantities'];
                $negativeQuantities = $productData['negative_quantities'];

                try {
                    // Process positive differences (add to stock)
                    if (!empty($positiveQuantities)) {
                        $this->stockServices->revaluateProductReservations(
                            $product,
                            fn() => $this->stockServices->addTo(
                                $product,
                                $positiveQuantities
                            )
                        );
                    }

                    // Process negative differences (remove from stock)
                    if (!empty($negativeQuantities)) {
                        $this->stockServices->revaluateProductReservations(
                            $product,
                            fn() => $this->stockServices->removeFromStock(
                                $product,
                                $negativeQuantities
                            )
                        );
                    }
                } catch (\Exception $e) {
                    if ($e->getCode() == 540) {
                        throw new \Exception("فشل تعديل كمية المنتج {$product->name}. يجب التأكد من توفر الكميات المطلوبة", 550);
                    }
                    throw $e;
                }
            }
        });
    }

    /**
     * Group quantities by product and calculate differences
     * @param \App\Models\StockCounting $stockCounting
     * @return array<array{product: mixed, positive_quantities: array, negative_quantities: array}>
     */
    protected function getQuantities(StockCounting $stockCounting): array
    {
        $productQuantities = [];
        $stockCounting->load('items.product');

        foreach ($stockCounting->items as $item) {
            if (!isset($productQuantities[$item->product_id])) {
                $productQuantities[$item->product_id] = [
                    'product' => $item->product,
                    'positive_quantities' => [],
                    'negative_quantities' => []
                ];
            }

            // Calculate old and new quantities in pieces
            $oldTotalPieces = ($item->old_packets_quantity * $item->product->packet_to_piece) + $item->old_piece_quantity;
            $newTotalPieces = ($item->new_packets_quantity * $item->product->packet_to_piece) + $item->new_piece_quantity;

            // Calculate difference
            $diffPieces = $newTotalPieces - $oldTotalPieces;

            // Add to appropriate array based on difference sign
            $releaseDateKey = $item->release_date->toDateString();

            if ($diffPieces > 0) {
                // Positive difference - add to stock
                $productQuantities[$item->product_id]['positive_quantities'][$releaseDateKey] = abs($diffPieces);
            } elseif ($diffPieces < 0) {
                // Negative difference - remove from stock
                $productQuantities[$item->product_id]['negative_quantities'][$releaseDateKey] = abs($diffPieces);
            }
            // If difference is zero, no action needed
        }

        return $productQuantities;
    }
}
