<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReceiptNote>
 */
class ReceiptNoteFactory extends Factory
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
            'status' => InvoiceStatus::DRAFT,
            'note_type' => fake()->randomElement(ReceiptNoteType::cases()),
            'officer_id' => User::factory(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
