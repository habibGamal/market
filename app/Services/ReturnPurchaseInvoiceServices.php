<?php

namespace App\Services;

use App\Models\ReturnPurchaseInvoice;
use DB;

class ReturnPurchaseInvoiceServices
{
    public function __construct(
        protected StockServices $stockServices,
    ) {
    }

    /**
     * Mark invoice items as unavailable in stock
     *
     * @param ReturnPurchaseInvoice $invoice The return purchase invoice
     * @return void
     * @throws \Exception If the quantity is not available in stock
     */
    public function processReturnPurchaseInvoice(ReturnPurchaseInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $productQuantities = $this->getQuantities($invoice);


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

    /**
     * Undo marking quantities as unavailable in stock
     *
     * @param ReturnPurchaseInvoice $invoice The return purchase invoice
     * @return void
     */
    public function deleteReturnPurchaseInvoice(ReturnPurchaseInvoice $invoice): void
    {
        if (!$invoice->closed) {
            $invoice->deleteQuietly();
            return;
        }
        if ($invoice->closed && $invoice->issue_note_id) {
            throw new \Exception('لا يمكن حذف المرتجع بعد صرفه');
        }
        DB::transaction(function () use ($invoice) {
            $productQuantities = $this->getQuantities($invoice);

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

            $invoice->deleteQuietly();
        });
    }

    /**
     * Get the quantities of products in the invoice
     *
     * @param ReturnPurchaseInvoice $invoice The return purchase invoice
     * @return array<array|array{product: mixed, quantities: array>}
     */
    protected function getQuantities(ReturnPurchaseInvoice $invoice): array
    {
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

            // Convert packets and pieces to total pieces
            $totalPieces = ($item->packets_quantity * $item->product->packet_to_piece) + $item->piece_quantity;
            $productQuantities[$item->product_id]['quantities'][$releaseDateKey] += $totalPieces;
        }
        return $productQuantities;
    }
}
