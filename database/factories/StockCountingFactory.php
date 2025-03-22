<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockCounting>
 */
class StockCountingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_diff' => fake()->randomFloat(2, -10000, 10000),
            'status' => fake()->randomElement([InvoiceStatus::DRAFT, InvoiceStatus::CLOSED]),
            'officer_id' => User::factory(),
            'note' => fake()->paragraph(),
        ];
    }
}
