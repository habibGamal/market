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
        $product = Product::factory()->create();
        $packets_quantity = fake()->numberBetween(1, 10);
        $piece_quantity = fake()->numberBetween(1, $product->packet_to_piece);
        $packet_price = fake()->randomFloat(2, 10, 100);
        $piece_price = $packet_price / $product->packet_to_piece;
        $packet_cost = fake()->randomFloat(2, 5, $packet_price - 1);

        return [
            'product_id' => $product->id,
            'packets_quantity' => $packets_quantity,
            'packet_price' => $packet_price,
            'packet_cost' => $packet_cost,
            'piece_quantity' => $piece_quantity,
            'piece_price' => $piece_price,
            'officer_id' => User::factory(),
            'order_id' => Order::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
