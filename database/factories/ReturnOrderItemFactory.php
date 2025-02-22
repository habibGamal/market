<?php

namespace Database\Factories;

use App\Enums\ReturnOrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReturnOrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'packets_quantity' => fake()->numberBetween(0, 10),
            'packet_price' => fake()->randomFloat(2, 5, 100),
            'piece_quantity' => fake()->numberBetween(0, 20),
            'piece_price' => fake()->randomFloat(2, 1, 20),
            'driver_id' => User::factory(),
            'status' => fake()->randomElement(ReturnOrderStatus::cases()),
            'return_reason' => fake()->sentence(),
            'order_id' => Order::factory(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
