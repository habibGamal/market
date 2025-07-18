<?php

namespace App\Observers;

use App\Models\PurchaseInvoice;

class PurchaseInvoiceObserver
{
    /**
     * Handle the PurchaseInvoice "created" event.
     */
    public function created(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "creating" event.
     */
    public function creating(PurchaseInvoice $purchaseInvoice): void
    {
        $purchaseInvoice->total = collect($purchaseInvoice->items)->sum(function ($item) {
            // Get the product to access packet_to_piece
            $product = \App\Models\Product::find($item['product_id']);
            $packetsQuantity = $item['packets_quantity'] ?? 0;
            $pieceQuantity = $item['piece_quantity'] ?? 0;
            $packetCost = $item['packet_cost'] ?? 0;

            // Calculate total using the new formula: (packets_quantity + piece_quantity / packet_to_piece) * packet_cost
            $totalPackets = $packetsQuantity + ($pieceQuantity / $product->packet_to_piece);
            return $totalPackets * $packetCost;
        });
        unset($purchaseInvoice->items);
    }

    /**
     * Handle the PurchaseInvoice "updated" event.
     */
    public function updated(PurchaseInvoice $purchaseInvoice): void
    {

    }

    /**
     * Handle the PurchaseInvoice "updating" event.
     */
    public function updating(PurchaseInvoice $purchaseInvoice): void
    {
        $purchaseInvoice->total = $purchaseInvoice->getRelationValue('items')->sum('total');
        // $purchaseInvoice->setOldItems($purchaseInvoice->items->toArray());
        // dd($purchaseInvoice->getRelationValue('items'));
        $purchaseInvoice->offsetUnset('items');
    }

    /**
     * Handle the PurchaseInvoice "deleted" event.
     */
    public function deleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "restored" event.
     */
    public function restored(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "force deleted" event.
     */
    public function forceDeleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }
}
