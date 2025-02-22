<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelledOrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'packets_quantity' => fake()->numberBetween(0, 10),
            'packet_price' => fake()->randomFloat(2, 5, 100),
            'piece_quantity' => fake()->numberBetween(0, 20),
            'piece_price' => fake()->randomFloat(2, 1, 20),
            'officer_id' => User::factory(),
            'order_id' => Order::factory(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
