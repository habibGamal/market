<?php
namespace App\Services;

use App\Models\Waste;
use DB;

class WasteServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {}

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
            // Load the items with products if not already loaded
            $waste->load('items.product');
            // Group quantities by product and release date
            $productQuantities = [];
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

            // Mark quantities as unavailable for each product
            foreach ($productQuantities as $productData) {
                $this->stockServices->unavailable(
                    $productData['product'],
                    $productData['quantities']
                );
            }
        });
    }
}
