<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('-1 month', '+1 month');
        $endAt = fake()->dateTimeBetween($startAt, '+3 months');

        return [
            'name' => fake()->words(3, true),
            'instructions' => [
                'discount_percentage' => fake()->numberBetween(5, 50),
                'minimum_order_amount' => fake()->numberBetween(100, 1000),
                'maximum_discount' => fake()->numberBetween(50, 500),
            ],
            'is_active' => fake()->boolean(70),
            'start_at' => $startAt,
            'end_at' => $endAt,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => now()->subMonths(2),
            'end_at' => now()->subMonth(),
        ]);
    }
}
