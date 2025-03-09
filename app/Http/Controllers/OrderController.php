<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\PlaceOrderServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PlaceOrderServices $placeOrderServices
    ) {}

    public function index()
    {
        $customer = auth('customer')->user();
        $orders = Order::where('customer_id', $customer->id)
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->count(),
                ];
            });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
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
            'returnItems.product'
        ]);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'net_total' => $order->net_total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->image,
                        ],
                        'packets_quantity' => $item->packets_quantity,
                        'packet_price' => $item->packet_price,
                        'piece_quantity' => $item->piece_quantity,
                        'piece_price' => $item->piece_price,
                        'total' => $item->total,
                    ];
                }),
                'cancelled_items' => $order->cancelledItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'name' => $item->product->name,
                        ],
                        'packets_quantity' => $item->packets_quantity,
                        'piece_quantity' => $item->piece_quantity,
                        'total' => $item->total,
                        'notes' => $item->notes,
                    ];
                }),
                'return_items' => $order->returnItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'name' => $item->product->name,
                        ],
                        'packets_quantity' => $item->packets_quantity,
                        'piece_quantity' => $item->piece_quantity,
                        'total' => $item->total,
                        'status' => $item->status,
                        'return_reason' => $item->return_reason,
                    ];
                }),
            ]
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
}
