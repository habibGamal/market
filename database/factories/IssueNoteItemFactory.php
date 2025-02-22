<?php

namespace Database\Factories;

use App\Models\IssueNoteItem;
use App\Models\Product;
use App\Models\IssueNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueNoteItemFactory extends Factory
{
    protected $model = IssueNoteItem::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'issue_note_id' => IssueNote::factory(),
            'packets_quantity' => $this->faker->numberBetween(1, 10),
            'piece_quantity' => $this->faker->numberBetween(1, 20),
            'packet_cost' => $this->faker->randomFloat(2, 50, 200),
            'release_date' => now(),
            'total' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
