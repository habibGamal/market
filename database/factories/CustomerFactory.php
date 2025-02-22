<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->streetAddress(),
            'gov' => $this->faker->state(),
            'city' => $this->faker->city(),
            'village' => $this->faker->cityPrefix(),
            'area_id' => Area::factory(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'whatsapp' => $this->faker->boolean(70) ? $this->faker->phoneNumber() : null,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'rating_points' => $this->faker->numberBetween(0, 100),
            'postpaid_balance' => $this->faker->randomFloat(2, 0, 10000),
            'blocked' => $this->faker->boolean(20),
        ];
    }
}
