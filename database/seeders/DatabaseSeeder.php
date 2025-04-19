<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Section;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory()->create([
            'name' => 'Main',
            'address' => 'Main.',
        ]);

        // Driver::factory(3)->create()->each(function ($driver) {
        //     $driver->account()->update([
        //         'balance' => 1000 // Setting initial balance of 1000
        //     ]);
        // });

        BusinessType::factory()->create([
            'name' => 'سوبرماركت',
        ]);

        Customer::factory()->create([
            'phone' => '01000000000',
            'phone_verified_at' => now(),
            'password' => 'review_password',
        ]);
        $this->call([
            UserSeeder::class,
            // ProductSeeder::class,
            ShieldSeeder::class,
            // PendingOrderSeeder::class,
            // SupplierSeeder::class,
            // ProductReportSeeder::class,
        ]);
    }
}
