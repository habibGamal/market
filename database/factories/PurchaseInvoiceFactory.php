<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseInvoice>
 */
class PurchaseInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement([InvoiceStatus::DRAFT, InvoiceStatus::CLOSED]),
            'officer_id' => User::factory(),
            'supplier_id' => \App\Models\Supplier::factory(),
        ];
    }
}
