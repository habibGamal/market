<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index()
    {
        $cart = $this->cartService->getOrCreateCart(auth('customer')->id());

        // Load cart items with their products and prices
        $cart->load(['items.product']);

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    public function updateQuantity(Request $request, CartItem $item)
    {
        try {
            $validated = $request->validate([
                'packets' => 'required|integer|min:0',
                'pieces' => 'required|integer|min:0',
            ]);

            $updatedItem = $this->cartService->updateItemQuantity(
                $item,
                $validated['packets'],
                $validated['pieces']
            );

            return response()->json([
                'message' => 'تم تحديث الكمية بنجاح',
                'cart_total' => $updatedItem->cart->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function addItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'packets' => 'required|integer|min:0',
                'pieces' => 'required|integer|min:0',
            ]);

            $cart = $this->cartService->getOrCreateCart(auth('customer')->id());
            $product = Product::findOrFail($validated['product_id']);

            $item = $this->cartService->addItem(
                $cart,
                $product,
                $validated['packets'],
                $validated['pieces']
            );

            return response()->json([
                'message' => 'تم إضافة المنتج إلى السلة',
                'cart_total' => $cart->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function removeItem(CartItem $item)
    {
        try {
            $cart = $item->cart;
            $this->cartService->deleteItem($item);

            return response()->json([
                'message' => 'تم حذف المنتج من السلة',
                'cart_total' => $cart->total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function empty()
    {
        try {
            $cart = $this->cartService->getOrCreateCart(auth('customer')->id());
            $this->cartService->emptyCart($cart);

            return response()->json([
                'message' => 'تم إفراغ السلة',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
