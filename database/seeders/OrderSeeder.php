<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderServices;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{

    public function run(): void
    {
        $orderServices = app(OrderServices::class);
        $customers = Customer::factory(3)->create();
        $products = Product::factory(10)->create();

        $customers->each(function (Customer $customer) use ($products,$orderServices) {
            // Create a pending order
            $pendingOrder = Order::factory()
                ->create(['customer_id' => $customer->id, 'status' => 'pending']);

            // Add 1-3 items to the pending order using OrderServices
            $pendingOrderItems = [];
            for ($i = 0; $i < fake()->numberBetween(1, 3); $i++) {
                $product = $products->random();
                $pendingOrderItems[] = [
                    'product_id' => $product->id,
                    'piece_quantity' => fake()->numberBetween(1, 5),
                    'piece_price' => fake()->randomFloat(2, 10, 50),
                ];
            }
            $orderServices->addOrderItems($pendingOrder, $pendingOrderItems);

            // Create a delivered order with some cancelled and returned items
            $deliveredOrder = Order::factory()
                ->create(['customer_id' => $customer->id]);

            // Add 2-4 regular items
            $deliveredOrderItems = [];
            for ($i = 0; $i < fake()->numberBetween(2, 4); $i++) {
                $product = $products->random();
                $deliveredOrderItems[] = [
                    'product_id' => $product->id,
                    'piece_quantity' => fake()->numberBetween(1, 5),
                    'piece_price' => fake()->randomFloat(2, 10, 50),
                ];
            }
            $orderServices->addOrderItems($deliveredOrder, $deliveredOrderItems);

            // Maybe add some cancelled items
            if (fake()->boolean(30)) {
                $cancelledItems = [];
                for ($i = 0; $i < fake()->numberBetween(1, 2); $i++) {
                    $product = $products->random();
                    $cancelledItems[] = [
                        'product_id' => $product->id,
                        'piece_quantity' => fake()->numberBetween(1, 3),
                        'piece_price' => fake()->randomFloat(2, 10, 50),
                        'officer_id' => User::factory()->create()->id,
                        'notes' => fake()->sentence(),
                    ];
                }
                $orderServices->cancelledItems($deliveredOrder, $cancelledItems);
            }

            // Maybe add some returned items
            if (fake()->boolean(20)) {
                $returnedItems = [];
                for ($i = 0; $i < fake()->numberBetween(1, 2); $i++) {
                    $product = $products->random();
                    $returnedItems[] = [
                        'product_id' => $product->id,
                        'piece_quantity' => fake()->numberBetween(1, 2),
                        'piece_price' => fake()->randomFloat(2, 10, 50),
                        'driver_id' => User::factory()->create()->id,
                        'return_reason' => fake()->sentence(),
                        'notes' => fake()->sentence(),
                        'status' => 'pending',
                    ];
                }
                $orderServices->returnItems($deliveredOrder, $returnedItems);
            }
        });
    }
}
