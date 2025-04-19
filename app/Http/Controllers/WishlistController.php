<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Display the customer's wishlist
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect()->route('login');
        }

        $wishlistProducts = $customer->wishlistProducts()
            ->with(['category', 'brand'])
            ->get();

        return Inertia::render('Wishlist/Index', [
            'products' => $wishlistProducts,
        ]);
    }

    /**
     * Add a product to the wishlist
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        // Check if product is already in wishlist
        $exists = $customer->wishlistProducts()
            ->where('product_id', $validated['product_id'])
            ->exists();

        if (!$exists) {
            // Add product to wishlist
            $customer->wishlistProducts()->attach($validated['product_id']);
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج إلى قائمة المفضلة');
    }

    /**
     * Remove a product from the wishlist
     */
    public function destroy(Request $request, $productId)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect()->route('login');
        }

        $customer->wishlistProducts()->detach($productId);

        return redirect()->back()->with('success', 'تمت إزالة المنتج من قائمة المفضلة');
    }
}
