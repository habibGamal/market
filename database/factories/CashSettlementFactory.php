<?php

namespace Database\Factories;

use App\Enums\CashSettlementStatus;
use App\Enums\CashSettlementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashSettlementFactory extends Factory
{
    public function definition(): array
    {
        $shouldPaidAt = $this->faker->dateBetween('-1 month', '+1 month');
        $isPaid = $this->faker->boolean(70);

        // If paid and should_paid_at is in the future, set paid_at to a random date before now
        $paidAt = null;
        if ($isPaid) {
            if ($shouldPaidAt > now()->toDateString()) {
                $paidAt = $this->faker->dateTimeBetween('-1 week', 'now');
            } else {
                $paidAt = $this->faker->dateTimeBetween($shouldPaidAt, 'now');
            }
        }

        return [
            'value' => $this->faker->randomFloat(2, 100, 10000),
            'notes' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(CashSettlementType::cases()),
            'officer_id' => User::factory(),
            'status' => $isPaid ? CashSettlementStatus::PAID : CashSettlementStatus::UNPAID,
            'paid_at' => $paidAt,
            'should_paid_at' => $shouldPaidAt,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashSettlementStatus::PAID,
            'paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashSettlementStatus::UNPAID,
            'paid_at' => null,
        ]);
    }

    public function incoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashSettlementType::IN,
        ]);
    }

    public function outgoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashSettlementType::OUT,
        ]);
    }
}
