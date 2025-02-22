<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => Order::factory(),
            'piece_quantity' => fake()->numberBetween(1, 10),
            'piece_price' => fake()->randomFloat(2, 5, 100),
            'total' => 0, // Will be calculated by observer
        ];
    }
}
