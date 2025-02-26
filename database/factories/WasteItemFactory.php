<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Waste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WasteItem>
 */
class WasteItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'packets_quantity' => fake()->numberBetween(1, 10),
            'piece_quantity' => fake()->numberBetween(0, 10),
            'packet_cost' => fake()->randomFloat(2, 50, 500),
            'release_date' => fake()->date(),
            'waste_id' => Waste::factory(),
            'total' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                if ($product && $product->packet_to_piece > 0) {
                    return ($attributes['packets_quantity'] * $attributes['packet_cost']) +
                          (($attributes['piece_quantity'] / $product->packet_to_piece) * $attributes['packet_cost']);
                }
                return $attributes['packets_quantity'] * $attributes['packet_cost'];
            },
        ];
    }
}
