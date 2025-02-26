<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderServices;
use App\Services\StockServices;
use Illuminate\Database\Seeder;

class PendingOrderSeeder extends Seeder
{
    public function run(): void
    {
        $orderServices = app(OrderServices::class);
        $stockServices = app(StockServices::class);
        $customers = Customer::factory(10)->create();
        $products = Product::factory(5)->create();

        // Add initial stock for products
        $products->each(
            fn(Product $product) => $stockServices->addTo($product, [
                '2025-02-12' => 10,
                '2025-02-13' => 2000,
            ])
        );

        $customers->each(function (Customer $customer) use ($products, $orderServices) {
            // Create a pending order
            $pendingOrder = Order::factory()
                ->create([
                    'customer_id' => $customer->id,
                    'created_at' => now()->subDay(),
                    'status' => 'pending',
                ]);

            // Add 5-8 items to the pending order using OrderServices
            $pendingOrderItems = [];
            for ($i = 0; $i < fake()->numberBetween(5, 8); $i++) {
                $product = $products->random();
                $pendingOrderItems[] = [
                    'product_id' => $product->id,
                    'packets_quantity' => fake()->numberBetween(1, 5),
                    'packet_price' => fake()->randomFloat(2, 10, 50),
                    'piece_quantity' => fake()->numberBetween(1, 5),
                    'piece_price' => fake()->randomFloat(2, 10, 50),
                ];
            }

            $orderServices->addOrderItems($pendingOrder, $pendingOrderItems);
        });
    }
}
