<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        // Cache key based on the normalized query
        $cacheKey = 'product_search:' . md5($query);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query) {
            // Split query into words and escape special characters
            $words = collect(explode(' ', $query))
                ->filter(fn($word) => mb_strlen($word) >= 2)
                ->map(fn($word) => DB::getPdo()->quote('%' . $word . '%'))
                ->toArray();

            if (empty($words)) {
                return [];
            }

            $conditions = collect($words)->map(function ($word) {
                return "REPLACE(REPLACE(REPLACE(name, 'أ', 'ا'), 'إ', 'ا'), 'ة', 'ه') LIKE $word" .
                    " OR barcode LIKE $word";
            })->join(' OR ');

            return Product::query()
                ->select(['id', 'name', 'image', 'category_id'])
                ->with(['category:id,name'])
                ->whereRaw("($conditions)")
                ->orderByRaw("CASE
                    WHEN name LIKE ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                END", ["{$query}%", "%{$query}%"])
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image ?? '/images/products/placeholder.jpg',
                        'category' => $product->category?->name ?? 'غير مصنف',
                    ];
                });
        });
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
