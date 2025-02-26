<?php

namespace App\Services;

use App\Models\ReturnPurchaseInvoice;
use DB;

class ReturnPurchaseInvoiceServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {}

    /**
     * Mark invoice items as unavailable in stock
     *
     * @param ReturnPurchaseInvoice $invoice The return purchase invoice
     * @return void
     * @throws \Exception If the quantity is not available in stock
     */
    public function markItemsAsUnavailable(ReturnPurchaseInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            // Load the items with products if not already loaded
            $invoice->loadMissing('items.product');

            // Group quantities by product and release date
            $productQuantities = [];
            foreach ($invoice->items as $item) {
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

                // Convert packets to pieces
                $totalPieces = $item->packets_quantity * $item->product->packet_to_piece;
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
