<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverAccountFactory extends Factory
{
    public function definition(): array
    {
        dd('DriverAccountFactory called');
        return [
            'driver_id' => User::factory(),
            'balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
