<?php

namespace Database\Factories;

use App\Enums\DriverStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_id' => User::factory(),
            'driver_assisment_officer_id' => User::factory(),
            'order_id' => Order::factory(),
            'status' => fake()->randomElement(DriverStatus::cases()),
        ];
    }
}
