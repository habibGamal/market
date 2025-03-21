<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnOrderItem;
use App\Notifications\Templates\OrderTemplate;
use App\Services\CartService;
use App\Services\NotificationService;
use App\Services\PlaceOrderServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PlaceOrderServices $placeOrderServices,
    ) {
    }

    public function index()
    {
        $customer = auth('customer')->user();
        $orders = Order::where('customer_id', $customer->id)
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'total' => $order->total,
                    'discount' => $order->discount,
                    'net_total' => $order->net_total,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->count(),
                ];
            });

        return Inertia::render('Orders/Index', [
            'orders' => inertia()->merge(
                $orders->items()
            ),
            'pagination' => Arr::except($orders->toArray(), ['data']),
        ]);
    }

    public function previewPlaceOrder(Request $request)
    {
        try {
            $cart = $this->cartService->getOrCreateCart(auth('customer')->id());

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index');
            }

            $preview = $this->placeOrderServices->previewOrder($cart);

            return Inertia::render('Cart/PlaceOrder', [
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function show(Order $order)
    {
        // Ensure the order belongs to the authenticated customer
        if ($order->customer_id !== auth('customer')->id()) {
            abort(403, 'غير مصرح بالوصول إلى هذا الطلب');
        }

        $order->load([
            'items.product',
            'cancelledItems.product',
            'returnItems.product',
            'offers'
        ])
            ->append([
                'net_total',
            ])
        ;

        // dd($order);
        return Inertia::render('Orders/Show', [
            'order' => $order
        ]);
    }

    public function placeOrder(Request $request)
    {
        try {
            $cart = $this->cartService->getOrCreateCart(auth('customer')->id());

            if ($cart->items->isEmpty()) {
                return response()->json([
                    'message' => 'لا يمكن إتمام الطلب. السلة فارغة.'
                ], 422);
            }

            $order = $this->placeOrderServices->placeOrder($cart);
            notifyCustomerWithOrderStatus($order);

            return response()->json([
                'message' => 'تم إتمام الطلب بنجاح',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function returns()
    {
        $customer = auth('customer')->user();
        $returnItems = ReturnOrderItem::query()
            ->whereHas('order', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->with(['order', 'product'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Returns/Index', [
            'returns' => inertia()->merge(
                $returnItems->items()
            ),
            'pagination' => Arr::except($returnItems->toArray(), ['data']),
        ]);
    }
}
