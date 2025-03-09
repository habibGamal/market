<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart(int $customerId): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customerId]);
    }

    public function addItem(Cart $cart, Product $product, int $packetsQuantity = 0, int $pieceQuantity = 0): CartItem
    {
        // Check if piece quantity exceeds packet size
        if ($pieceQuantity >= $product->packet_to_piece) {
            throw new \Exception(sprintf('عدد القطع يجب أن يكون أقل من حجم الباكيت (%d قطعة)', $product->packet_to_piece));
        }

        // Check product availability
        $availablePieces = $product->getAvailablePiecesQuantityAttribute();
        $requestedPieces = ($packetsQuantity * $product->packet_to_piece) + $pieceQuantity;

        if ($availablePieces < $requestedPieces) {
            throw new \Exception(sprintf(
                'الكمية المطلوبة غير متوفرة. الكمية المتوفرة: %d باكيت و %d قطعة',
                floor($availablePieces / $product->packet_to_piece),
                $availablePieces % $product->packet_to_piece
            ));
        }

        return DB::transaction(function () use ($cart, $product, $packetsQuantity, $pieceQuantity) {
            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);

            $item->packets_quantity = $packetsQuantity;
            $item->piece_quantity = $pieceQuantity;
            $item->save();

            $this->updateCartTotal($cart);

            return $item;
        });
    }

    public function updateItemQuantity(CartItem $item, int $packetsQuantity = 0, int $pieceQuantity = 0): CartItem
    {
        // Check product availability
        $product = $item->product;

        // Check if piece quantity exceeds packet size
        if ($pieceQuantity >= $product->packet_to_piece) {
            throw new \Exception(sprintf('عدد القطع يجب أن يكون أقل من حجم الباكيت (%d قطعة)', $product->packet_to_piece));
        }

        // Check produc
        $availablePieces = $product->getAvailablePiecesQuantityAttribute();
        $requestedPieces = ($packetsQuantity * $product->packet_to_piece) + $pieceQuantity;

        if ($availablePieces < $requestedPieces) {throw new \Exception(sprintf(
            'الكمية المطلوبة غير متوفرة. الكمية المتوفرة: %d باكيت و %d قطعة',
            floor($availablePieces / $product->packet_to_piece),
            $availablePieces % $product->packet_to_piece
        ));
        }

        return DB::transaction(function () use ($item, $product, $packetsQuantity, $pieceQuantity) {
            $item->packets_quantity = $packetsQuantity;
            $item->piece_quantity = $pieceQuantity;
            $item->save();

            $this->updateCartTotal($item->cart);

            return $item;
        });
    }

    public function deleteItem(CartItem $item): void
    {
        DB::transaction(function () use ($item) {
            $cart = $item->cart;
            $item->delete();
            $this->updateCartTotal($cart);
        });
    }

    public function emptyCart(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->items()->delete();
            $cart->total = 0;
            $cart->save();
        });
    }

    private function updateCartTotal(Cart $cart): void
    {
        $total = $cart->items->sum('total');
        $cart->update(['total' => $total]);
    }
}
