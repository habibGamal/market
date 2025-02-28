<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();
        $packets_quantity = fake()->numberBetween(1, 10);
        $piece_quantity = fake()->numberBetween(1, $product->packet_to_piece);
        $packet_price = $product->packet_price;
        $piece_price = $product->piece_price;
        $packet_cost = $product->packet_cost;

        return [
            'product_id' => $product->id,
            'packets_quantity' => $packets_quantity,
            'packet_price' => $packet_price,
            'packet_cost' => $packet_cost,
            'piece_quantity' => $piece_quantity,
            'piece_price' => $piece_price,
            'order_id' => Order::factory(),
        ];
    }
}
