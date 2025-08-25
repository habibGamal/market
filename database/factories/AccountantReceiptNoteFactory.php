<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\IssueNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountantReceiptNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from_model_type' => null,
            'from_model_id' => null,
            'paid' => $this->faker->randomFloat(2, 100, 10000),
            'notes' => $this->faker->sentence(),
            'officer_id' => null,
        ];
    }

    public function byOfficer(User $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'officer_id' => $officer->id,
        ]);
    }

    public function forDriver(Driver $driver): static
    {
        return $this->state(fn (array $attributes) => [
            'from_model_type' => Driver::class,
            'from_model_id' => $driver->id,
        ]);
    }

    public function forIssueNote(IssueNote $issueNote): static
    {
        return $this->state(fn (array $attributes) => [
            'from_model_type' => IssueNote::class,
            'from_model_id' => $issueNote->id,
        ]);
    }
}