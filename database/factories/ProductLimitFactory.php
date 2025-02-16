<?php

namespace Database\Factories;

use App\Models\ProductLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductLimit>
 */
class ProductLimitFactory extends Factory
{
    protected $model = ProductLimit::class;

    public function definition(): array
    {
        return [
            'area_id' => \App\Models\Area::factory(
                ['name' => $this->faker->unique(true)->word]
            ),
            'product_id' => \App\Models\Product::factory(),
            'min_packets' => $this->faker->numberBetween(1, 10),
            'max_packets' => $this->faker->numberBetween(11, 20),
            'min_pieces' => $this->faker->numberBetween(1, 10),
            'max_pieces' => $this->faker->numberBetween(11, 20),
        ];
    }
}
