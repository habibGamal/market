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
        $product = Product::factory()->create();
        $packets_quantity = fake()->numberBetween(1, 10);
        $piece_quantity = fake()->numberBetween(1, $product->packet_to_piece);
        $packet_price = fake()->randomFloat(2, 10, 100);
        $piece_price = $packet_price / $product->packet_to_piece;
        $packet_cost = fake()->randomFloat(2, 5, $packet_price - 1);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'packets_quantity' => $packets_quantity,
            'packet_price' => $packet_price,
            'packet_cost' => $packet_cost,
            'piece_quantity' => $piece_quantity,
            'piece_price' => $piece_price,
            'driver_id' => User::factory(),
            'return_reason' => fake()->sentence(),
            'notes' => fake()->paragraph(),
            'status' => fake()->randomElement(ReturnOrderStatus::cases()),
        ];
    }
}
