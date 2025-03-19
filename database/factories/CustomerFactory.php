<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Gov;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        $gov = Gov::factory();
        $city = City::factory()->for($gov);
        $area = Area::factory()->for($city);

        return [
            'name' => $this->faker->name(),
            'location' => $this->faker->streetAddress(),
            'gov_id' => $gov,
            'city_id' => $city,
            'village' => $this->faker->cityPrefix(),
            'area_id' => $area,
            'address' => $this->faker->address(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'whatsapp' => $this->faker->boolean(70) ? $this->faker->phoneNumber() : null,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'rating_points' => $this->faker->numberBetween(0, 100),
            'postpaid_balance' => $this->faker->randomFloat(2, 0, 10000),
            'blocked' => $this->faker->boolean(20),
        ];
    }
}
