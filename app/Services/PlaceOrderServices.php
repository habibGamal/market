<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SettingKey;
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
        private readonly OfferService $offerService,
        private readonly CartService $cartService,
        private readonly CustomerPointsService $customerPointsService
    ) {
    }

    /**
     * Place an order from the customer's cart
     *
     * @throws \Exception if cart total doesn't match DB, if product limits are exceeded, or if stock reservation fails
     */
    public function placeOrder(Cart $cart): Order
    {
        return DB::transaction(function () use ($cart) {
            // Check if customer is active (not blocked)
            if ($cart->customer->blocked) {
                throw new \Exception('لا يمكن إتمام الطلب، حسابك موقوف حالياً');
            }

            // Check if cart exists and has items
            if (!$cart || $cart->items->isEmpty()) {
                throw new \Exception('لا يمكن إنشاء طلب فارغ، يرجى إضافة منتجات إلى السلة');
            }

            // Check if selling is stopped in settings
            if (settings(SettingKey::STOP_SELLING, false)) {
                throw new \Exception('تم إيقاف البيع مؤقتاً، يرجى المحاولة لاحقاً');
            }

            // Check for inactive products
            foreach ($cart->items as $item) {
                if (!$item->product->is_active) {
                    throw new \Exception("المنتج {$item->product->name} غير متاح حالياً");
                }
            }

            // Re-evaluate cart total
            $recalculatedTotal = $this->recalculateCartTotal($cart);

            // Check for zero total
            if ($recalculatedTotal <= 0) {
                throw new \Exception('لا يمكن إنشاء طلب فارغ، يرجى إضافة منتجات إلى السلة');
            }

            // Cast to decimal for consistent comparison
            $recalculatedTotal = (float) number_format($recalculatedTotal, 2, '.', '');

            // For debugging
            if ($recalculatedTotal != $cart->total) {
                throw new \Exception('هناك تغييرات في الأسعار أو العروض. يرجى تحديث السلة');
            }

            // Convert cart items to order items format
            $cartItems = $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'packets_quantity' => $item->packets_quantity,
                    'packet_price' => $item->product->packet_price,
                    'packet_cost' => $item->product->packet_cost,
                    'piece_quantity' => $item->piece_quantity,
                    'piece_price' => $item->product->piece_price,
                ];
            })->toArray();

            // Get or create today's order
            $order = $this->getOrCreateTodayOrder($cart->customer_id);

            // Add items to order
            $this->orderServices->addOrderItems($order, $cartItems);

            // Order Validation & Evaluation
            $skipIfAdmins = in_array(auth()->user()->id, [1, 2, 3]);
            $this->orderEvaluation($order, $skipIfAdmins);

            // add points to the customer based on total cart (as total order is accumilative)
            $this->customerPointsService->addPoints($order->customer, $recalculatedTotal);

            // Clean up cart
            $this->cartService->emptyCart($cart);

            return $order;
        });
    }

    /**
     * Validate order and evaluate total, limits and minimum requirements
     *
     * @throws \Exception if validation fails
     */
    public function orderEvaluation(Order $order, $skipValidation = false): void
    {
        if ($skipValidation === false) {
            // ensure order total satisfies minimum order total
            $minTotalOrder = (float) settings(SettingKey::MIN_TOTAL_ORDER, 0);
            if ($order->total < $minTotalOrder) {
                throw new \Exception("الحد الأدنى لإجمالي الطلب هو $minTotalOrder");
            }

            // Check product limits for customer area
            $this->validateProductLimits($order);
        }

        // apply offers
        $discountData = $this->offerService->calculateOrderDiscount($order);
        $order->offers()->sync($discountData['applied_offers']->pluck('id'));
        $order->discount = $discountData['discount'];
        $order->save();
    }

    /**
     * Preview order details by running placeOrder in a transaction that will rollback
     */
    public function previewOrder(Cart $cart): array
    {
        $preview = [];
        try {
            DB::transaction(function () use ($cart, &$preview) {
                // Run the actual placeOrder logic to get real calculations
                $order = $this->placeOrder($cart);

                // Get all the order data we need before rolling back
                $preview = [
                    'items' => $order->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'packets_quantity' => $item->packets_quantity,
                            'piece_quantity' => $item->piece_quantity,
                            'packet_price' => $item->packet_price,
                            'piece_price' => $item->piece_price,
                            'product' => $item->product,
                            'total' => ($item->packets_quantity * $item->packet_price) +
                                ($item->piece_quantity * $item->piece_price)
                        ];
                    })->toArray(),
                    'subtotal' => (float) $order->total,
                    'discount' => $order->discount,
                    'total' => $order->netTotal,
                    'applied_offers' => $order->offers
                ];

                // Force a rollback by throwing an exception
                throw new \Exception('Preview rollback');

            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Preview rollback') {
                return $preview;
            }
            throw $e;
        }

        return $preview; // Add missing return
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
    private function validateProductLimits(Order $order): void
    {
        foreach ($order->items as $item) {
            // check limits for each product
            $limit = ProductLimit::where('product_id', $item->product_id)
                ->where('area_id', $order->customer->area_id)
                ->first();

            if (!$limit)
                continue;

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

            // Check if product allows selling pieces
            if (!$item->product->can_sell_pieces && $item->piece_quantity > 0) {
                throw new \Exception("لا يمكن بيع قطع فردية من المنتج {$item->product->name}. يمكن شراء عبوات فقط.");
            }
        }
    }

    /**
     * Get today's order for customer or create new one
     */
    private function getOrCreateTodayOrder(int $customerId): Order
    {
        $today = Carbon::today('Africa/Cairo');
        $order = null;

        $lastPendingOrder = Order::where('customer_id', $customerId)
            ->where('status', OrderStatus::PENDING->value)
            ->latest()
            ->first();

        // If there's a pending order, check if it was created today in Cairo timezone
        if ($lastPendingOrder) {
            $lastPendingOrderDate = Carbon::parse($lastPendingOrder->created_at)->timezone('Africa/Cairo')->startOfDay();
            $todayDate = $today->copy()->startOfDay();
            // If the last pending order was created today, use it
            if ($lastPendingOrderDate->equalTo($todayDate)) {
                $order = $lastPendingOrder;
            }
        }

        if ($order) {
            return $order;
        } else {
            return Order::create(
                [
                    'status' => OrderStatus::PENDING->value,
                    'customer_id' => $customerId,
                    'total' => 0,
                ]
            );
        }
    }
}
