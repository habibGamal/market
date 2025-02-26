<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ReturnPurchaseInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReturnPurchaseInvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'product_id' => $product->id,
            'packets_quantity' => $this->faker->numberBetween(1, 10),
            'piece_quantity' => $this->faker->numberBetween(1, $product->packet_to_piece),
            'packet_cost' => $product->packet_cost,
            'release_date' => $this->faker->date(),
            'return_purchase_invoice_id' => ReturnPurchaseInvoice::factory(),
            'total' => 0, // Will be calculated by observer
        ];
    }
}
