<?php
namespace App\Services;

use App\Models\Waste;
use DB;

class WasteServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {
    }

    /**
     * Process waste items and mark them as unavailable in stock
     *
     * @param Waste $waste The waste record
     * @return void
     * @throws \Exception If the quantity is not available in stock
     */
    public function processWaste(Waste $waste): void
    {
        DB::transaction(function () use ($waste) {
            $productQuantities = $this->getQuantities($waste);

            // Mark quantities as unavailable for each product
            foreach ($productQuantities as $productData) {
                try {
                    $this->stockServices->revaluateProductReservations(
                        $productData['product'],
                        fn() => $this->stockServices->unavailable(
                            $productData['product'],
                            $productData['quantities']
                        )
                    );
                } catch (\Exception $e) {
                    if ($e->getCode() == 540) {
                        throw new \Exception("فشل نقل حجز الكمية المطلوبة للمنتج {$productData['product']->name}. يجب توفير مخزون إضافي", 550);
                    }
                    throw $e;
                }
            }
        });
    }


    public function deleteWaste(Waste $waste): void
    {
        if (!$waste->closed) {
            $waste->deleteQuietly();
            return;
        }
        if ($waste->closed && $waste->issue_note_id) {
            throw new \Exception('لا يمكن حذف الهالك بعد صرفه');
        }
        DB::transaction(function () use ($waste) {
            $productQuantities = $this->getQuantities($waste);

            // Undo marking quantities as unavailable for each product
            foreach ($productQuantities as $productData) {
                $this->stockServices->revaluateProductReservations(
                    $productData['product'],
                    fn() => $this->stockServices->undoUnavailable(
                        $productData['product'],
                        $productData['quantities']
                    )
                );
            }

            $waste->deleteQuietly();
        });
    }

    /**
     * Group quantities by product and release date
     * @param \App\Models\Waste $waste
     * @return array<array|array{product: mixed, quantities: array>}
     */
    protected function getQuantities(Waste $waste): array
    {
        $productQuantities = [];
        $waste->load('items.product');
        // Group quantities by product and release date
        foreach ($waste->items as $item) {
            if (!isset($productQuantities[$item->product_id])) {
                $productQuantities[$item->product_id] = [
                    'product' => $item->product,
                    'quantities' => []
                ];
            }

            // Add quantities by release date
            $releaseDateKey = $item->release_date->toDateString();
            if (!isset($productQuantities[$item->product_id]['quantities'][$releaseDateKey])) {
                $productQuantities[$item->product_id]['quantities'][$releaseDateKey] = 0;
            }

            // Calculate total pieces (packets converted to pieces + individual pieces)
            $packetPieces = $item->packets_quantity * $item->product->packet_to_piece;
            $totalPieces = $packetPieces + $item->piece_quantity;

            $productQuantities[$item->product_id]['quantities'][$releaseDateKey] += $totalPieces;
        }
        return $productQuantities;
    }


}
