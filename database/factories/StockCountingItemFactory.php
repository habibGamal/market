<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockCounting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockCountingItem>
 */
class StockCountingItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oldPacketsQuantity = fake()->numberBetween(1, 50);
        $oldPieceQuantity = fake()->numberBetween(0, 10);
        $newPacketsQuantity = fake()->numberBetween(1, 50);
        $newPieceQuantity = fake()->numberBetween(0, 10);

        return [
            'product_id' => Product::factory(),
            'old_packets_quantity' => $oldPacketsQuantity,
            'old_piece_quantity' => $oldPieceQuantity,
            'new_packets_quantity' => $newPacketsQuantity,
            'new_piece_quantity' => $newPieceQuantity,
            'packet_cost' => fake()->randomFloat(2, 50, 500),
            'release_date' => fake()->date(),
            'stock_counting_id' => StockCounting::factory(),
            'total_diff' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                if ($product && $product->packet_to_piece > 0) {
                    $oldTotalInPieces = ($attributes['old_packets_quantity'] * $product->packet_to_piece) + $attributes['old_piece_quantity'];
                    $newTotalInPieces = ($attributes['new_packets_quantity'] * $product->packet_to_piece) + $attributes['new_piece_quantity'];
                    $diffInPieces = $newTotalInPieces - $oldTotalInPieces;
                    return ($diffInPieces / $product->packet_to_piece) * $attributes['packet_cost'];
                }
                return 0;
            },
        ];
    }
}
