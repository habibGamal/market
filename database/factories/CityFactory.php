<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Gov;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city(),
            'gov_id' => Gov::factory(),
        ];
    }
}
