<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expense_type_id' => ExpenseType::factory(),
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'notes' => $this->faker->sentence(),
            'approved_by' => User::factory(),
            'accountant_id' => User::factory(),
        ];
    }
}
