<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory()->create([
            'name' => 'Warehouse 1',
            'address' => '123 Fake St.',
        ]);

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            ShieldSeeder::class,
        ]);
    }
}
