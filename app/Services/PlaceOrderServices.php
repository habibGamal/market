<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductLimit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlaceOrderServices
{
    public function __construct(
        private readonly StockServices $stockServices,
        private readonly OrderServices $orderServices,
    ) {}

    /**
     * Place an order from the customer's cart
     *
     * @throws \Exception if cart total doesn't match DB, if product limits are exceeded, or if stock reservation fails
     */
    public function placeOrder(Cart $cart): Order
    {
        return DB::transaction(function () use ($cart) {
            // Re-evaluate cart total
            $recalculatedTotal = $this->recalculateCartTotal($cart);
            // Cast to decimal for consistent comparison
            $recalculatedTotal = (float) number_format($recalculatedTotal, 2, '.', '');
            $cartTotal = (float) number_format($cart->total, 2, '.', '');

            // For debugging
            if ($recalculatedTotal != $cart->total) {
                throw new \Exception('هناك تغييرات في الأسعار أو العروض. يرجى تحديث السلة');
            }

            // Check product limits for customer area
            $this->validateProductLimits($cart);

            // Get or create today's order
            $order = $this->getOrCreateTodayOrder($cart->customer_id);

            // Convert cart items to order items format
            $orderItems = $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'packets_quantity' => $item->packets_quantity,
                    'packet_price' => $item->product->packet_price,
                    'packet_cost' => $item->product->packet_cost,
                    'piece_quantity' => $item->piece_quantity,
                    'piece_price' => $item->product->piece_price,
                ];
            })->toArray();

            // Add items to order
            $this->orderServices->addOrderItems($order, $orderItems);

            // Clean up cart
            $cart->items()->delete();
            $cart->update(['total' => 0]);

            return $order;
        });
    }

    /**
     * Recalculate cart total based on current prices
     */
    private function recalculateCartTotal(Cart $cart): float
    {
        return $cart->items->sum(function ($item) {
            return ($item->packets_quantity * $item->product->packet_price) +
                   ($item->piece_quantity * $item->product->piece_price);
        });
    }

    /**
     * Validate product quantities against area limits
     *
     * @throws \Exception if limits are exceeded
     */
    private function validateProductLimits(Cart $cart): void
    {
        foreach ($cart->items as $item) {
            $limit = ProductLimit::where('product_id', $item->product_id)
                               ->where('area_id', $cart->customer->area_id)
                               ->first();

            if (!$limit) continue;

            if ($item->packets_quantity > $limit->max_packets) {
                throw new \Exception("تجاوز الحد الأقصى لعدد العبوات للمنتج {$item->product->name}. الحد الأقصى هو {$limit->max_packets}");
            }

            if ($item->packets_quantity < $limit->min_packets) {
                throw new \Exception("لم يتم الوصول للحد الأدنى لعدد العبوات للمنتج {$item->product->name}. الحد الأدنى هو {$limit->min_packets}");
            }

            if ($item->piece_quantity > $limit->max_pieces) {
                throw new \Exception("تجاوز الحد الأقصى لعدد القطع للمنتج {$item->product->name}. الحد الأقصى هو {$limit->max_pieces}");
            }

            if ($item->piece_quantity < $limit->min_pieces) {
                throw new \Exception("لم يتم الوصول للحد الأدنى لعدد القطع للمنتج {$item->product->name}. الحد الأدنى هو {$limit->min_pieces}");
            }
        }
    }

    /**
     * Get today's order for customer or create new one
     */
    private function getOrCreateTodayOrder(int $customerId): Order
    {
        $today = Carbon::today();

        return Order::firstOrCreate(
            [
                'customer_id' => $customerId,
                'created_at' => $today,
            ],
            [
                'total' => 0,
                'status' => OrderStatus::PENDING,
            ]
        );
    }
}
