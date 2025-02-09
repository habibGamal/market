<?php

namespace Database\Factories;

use App\Models\Product;
use App\Enums\ExpirationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'image' => $this->faker->imageUrl(),
            'barcode' => $this->faker->unique()->ean13,
            'packet_cost' => $this->faker->randomFloat(2, 1, 100),
            'packet_price' => $this->faker->randomFloat(2, 1, 150),
            'piece_price' => $this->faker->randomFloat(2, 1, 10),
            'expiration_duration' => $this->faker->numberBetween(1, 365),
            'expiration_unit' => $this->faker->randomElement(ExpirationUnit::values()),
            'before_discount' => [
                'packet_price' => $this->faker->randomFloat(2, 1, 150),
                'piece_price' => $this->faker->randomFloat(2, 1, 10),
            ],
            'packet_to_piece' => $this->faker->numberBetween(1, 24),
            'limits' => [
                'min_packets' => $this->faker->numberBetween(1, 10),
                'max_packets' => $this->faker->numberBetween(10, 100),
                'min_pieces' => $this->faker->numberBetween(1, 10),
                'max_pieces' => $this->faker->numberBetween(10, 100),
            ],
            'brand_id' => \App\Models\Brand::factory(),
            'category_id' => \App\Models\Category::factory(),
        ];
    }
}
