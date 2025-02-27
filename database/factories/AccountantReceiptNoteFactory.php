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
        $fromModel = $this->faker->randomElement([
            Driver::class,
            IssueNote::class,
        ]);

        return [
            'from_model_type' => $fromModel,
            'from_model_id' => $fromModel::factory(),
            'paid' => $this->faker->randomFloat(2, 100, 10000),
            'notes' => $this->faker->sentence(),
            'officer_id' => User::factory(),
        ];
    }
}
