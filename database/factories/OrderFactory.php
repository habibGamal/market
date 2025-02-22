<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    public function delivered()
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::DELIVERED,
        ]);
    }
}
