<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashSettlementAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Account',
            'inlet_name_alias' => $this->faker->word() . ' In',
            'outlet_name_alias' => $this->faker->word() . ' Out',
        ];
    }
}
