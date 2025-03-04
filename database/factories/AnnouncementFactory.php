<?php

namespace Database\Factories;

use App\Models\BusinessType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'text' => fake()->paragraph(),
            'color' => fake()->hexColor(),
            'active' => fake()->boolean(),
            'business_type_id' => BusinessType::factory()
        ];
    }
}
