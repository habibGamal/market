<?php

namespace App\Observers;

use App\Models\ReturnPurchaseInvoiceItem;

class ReturnPurchaseInvoiceItemObserver
{
    public function saving(ReturnPurchaseInvoiceItem $item): void
    {
        // Load product if not already loaded
        if (!$item->relationLoaded('product')) {
            $item->load('product');
        }

        $product = $item->product;
        $packetsQuantity = $item->packets_quantity ?? 0;
        $pieceQuantity = $item->piece_quantity ?? 0;
        $packetCost = $item->packet_cost ?? 0;

        // Calculate total using the new formula: (packets_quantity + piece_quantity / packet_to_piece) * packet_cost
        $totalPackets = $packetsQuantity + ($pieceQuantity / $product->packet_to_piece);
        $item->total = $totalPackets * $packetCost;
    }
}
