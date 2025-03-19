<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Gov;

class GovFactory extends Factory
{
    protected $model = Gov::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique(true)->city,
        ];
    }
}
