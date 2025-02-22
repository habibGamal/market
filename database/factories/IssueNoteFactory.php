<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\IssueNoteType;
use App\Models\IssueNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueNoteFactory extends Factory
{
    protected $model = IssueNote::class;

    public function definition(): array
    {
        return [
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'status' => InvoiceStatus::DRAFT,
            'officer_id' => User::factory(),
            'note_type' => IssueNoteType::ORDERS,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
