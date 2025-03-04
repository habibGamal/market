<?php

namespace Database\Factories;

use App\Models\BusinessType;
use App\Enums\SectionLocation;
use App\Enums\SectionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'business_type_id' => BusinessType::factory(),
            'location' => $this->faker->randomElement(SectionLocation::values()),
            'section_type' => $this->faker->randomElement(SectionType::values()),
        ];
    }
}
