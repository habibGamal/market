<?php

namespace App\Observers;

use App\Models\PurchaseInvoiceItem;

class PurchaseInvoiceItemObserver
{
    /**
     * Handle the PurchaseInvoiceItem "created" event.
     */
    public function created(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "updated" event.
     */
    public function updated(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "saving" event.
     */
    public function saving(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        // Load product if not already loaded
        if (!$purchaseInvoiceItem->relationLoaded('product')) {
            $purchaseInvoiceItem->load('product');
        }

        $product = $purchaseInvoiceItem->product;
        $packetsQuantity = $purchaseInvoiceItem->packets_quantity ?? 0;
        $pieceQuantity = $purchaseInvoiceItem->piece_quantity ?? 0;
        $packetCost = $purchaseInvoiceItem->packet_cost ?? 0;

        // Calculate total using the new formula: (packets_quantity + piece_quantity / packet_to_piece) * packet_cost
        $totalPackets = $packetsQuantity + ($pieceQuantity / $product->packet_to_piece);
        $purchaseInvoiceItem->total = $totalPackets * $packetCost;
    }

    /**
     * Handle the PurchaseInvoiceItem "deleted" event.
     */
    public function deleted(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "restored" event.
     */
    public function restored(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoiceItem "force deleted" event.
     */
    public function forceDeleted(PurchaseInvoiceItem $purchaseInvoiceItem): void
    {
        //
    }
}
