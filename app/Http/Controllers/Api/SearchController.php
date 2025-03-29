<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'q' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{Arabic}\p{L}0-9\s\-\_\.]+$/u']
        ]);

        if ($validator->fails()) {
            return response()->json([], 200);
        }

        $query = $this->normalizeArabicText($request->get('q'));

        // Use TNTSearch with Laravel Scout directly without caching
        $products = Product::search($query)->get()->sortByDesc('__tntSearchScore__');

        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image ?? '/images/products/placeholder.jpg',
                'category' => $product->category?->name ?? 'غير مصنف',
            ];
        })->values();
    }

    private function normalizeArabicText(string $text): string
    {
        $text = trim($text);

        // Remove tashkeel (diacritics)
        $text = preg_replace('/[\x{064B}-\x{065F}]/u', '', $text);

        // Remove special characters except Arabic letters, numbers, spaces, and basic punctuation
        $text = preg_replace('/[^\p{Arabic}\p{L}0-9\s\-\_\.]/u', '', $text);

        // Normalize alef variations (أ, إ, آ -> ا)
        $text = preg_replace('/[أإآ]/u', 'ا', $text);

        // Normalize teh marbuta (ة -> ه)
        $text = preg_replace('/ة/u', 'ه', $text);

        return $text;
    }
}
