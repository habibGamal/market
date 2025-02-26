<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReturnPurchaseInvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'total' => 0,
            'issue_note_id' => null,
            'status' => InvoiceStatus::DRAFT,
            'officer_id' => User::factory(),
            'supplier_id' => User::factory()->create(['role' => 'supplier']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
