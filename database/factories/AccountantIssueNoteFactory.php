<?php

namespace Database\Factories;

use App\Models\ReceiptNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountantIssueNoteFactory extends Factory
{
    public function definition(): array
    {
        $forModel = ReceiptNote::factory()->create();

        return [
            'for_model_id' => $forModel->id,
            'for_model_type' => $forModel::class,
            'paid' => fake()->randomFloat(2, 100, 1000),
            'notes' => fake()->optional()->sentence(),
            'officer_id' => User::factory(),
        ];
    }
}
