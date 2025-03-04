<?php

namespace Database\Factories;

use App\Models\BusinessType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SliderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => fake()->imageUrl(800, 400),
            'link' => fake()->url(),
            'sort_order' => fake()->numberBetween(0, 100),
            'active' => fake()->boolean(),
            'business_type_id' => BusinessType::factory()
        ];
    }
}
