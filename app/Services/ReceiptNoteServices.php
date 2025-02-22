<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Models\Driver;
use App\Models\PurchaseInvoice;
use App\Models\ReceiptNote;
use DB;

class ReceiptNoteServices
{
    public function __construct(
        protected StockServices $stockServices,
        protected ProductManageCostServices $productManageCostServices
    ) {
    }

    public function toStock(ReceiptNote $receiptNote)
    {
        !$receiptNote->relationLoaded('items') && $receiptNote->load('items.product');

        DB::transaction(function () use ($receiptNote) {
            $receiptNote->items->each(function ($item) {
                $this->productManageCostServices->updateCost($item->product, $item->totalQuantityByPiece, $item->packet_cost);
                $this->stockServices->addTo($item->product, $item->quantityReleases);
            });
        });
    }

    public function createFromPurchaseInvoice(PurchaseInvoice $purchaseInvoice): ReceiptNote
    {
        $receipt = $purchaseInvoice->receipt()->create([
            'note_type' => ReceiptNoteType::PURCHASES,
            'status' => InvoiceStatus::DRAFT,
            'total' => $purchaseInvoice->total,
            'officer_id' => auth()->id(),
        ]);

        $purchaseInvoice->receipt()->associate($receipt)->save();

        $receipt->items()->createMany(
            $purchaseInvoice->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'packets_quantity' => $item->packets_quantity,
                    'packet_cost' => $item->packet_cost,
                    'piece_quantity' => 0,
                    'release_dates' => [
                        [
                            'piece_quantity' => $item->packets_quantity * $item->product->packet_to_piece,
                            'release_date' => now()->format('Y-m-d'),
                        ]
                    ],
                    'reference_state' => $item->toArray(),
                    'total' => $item->total,
                ];
            })
        );

        return $receipt;
    }

    public function createFromDriverReturns(Driver $driver): ReceiptNote
    {
        $receipt = ReceiptNote::create([
            'note_type' => ReceiptNoteType::RETURN_ORDERS,
            'status' => InvoiceStatus::DRAFT,
            'total' => 0, // Will be calculated based on items
            'officer_id' => auth()->id(),
        ]);

        $receipt->items()->createMany(
            $driver->returnedProducts()
                ->withPivot('packets_quantity', 'piece_quantity')
                ->get()
                ->map(function ($product) {
                    return [
                        'product_id' => $product->id,
                        'packets_quantity' => $product->pivot->packets_quantity,
                        'piece_quantity' => $product->pivot->piece_quantity,
                        'packet_cost' => $product->packet_price,
                        'release_dates' => [
                            [
                                'piece_quantity' => ($product->pivot->packets_quantity * $product->packet_to_piece) + $product->pivot->piece_quantity,
                                'release_date' => now()->format('Y-m-d'),
                            ]
                        ],
                        'reference_state' => [
                            'packets_quantity' => $product->pivot->packets_quantity,
                            'piece_quantity' => $product->pivot->piece_quantity,
                        ],
                        'total' => ($product->pivot->packets_quantity * $product->packet_price) +
                                 ($product->pivot->piece_quantity * ($product->packet_price / $product->packet_to_piece))
                    ];
                })
        );

        // Update total
        $receipt->update(['total' => $receipt->items()->sum('total')]);

        // Clear driver's returned products
        // $driver->returnedProducts()->detach();

        return $receipt;
    }
}
