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
            'name' => 'Warehouse 1',
            'address' => '123 Fake St.',
        ]);

        Driver::factory(3)->create()->each(function ($driver) {
            $driver->account()->update([
                'balance' => 1000 // Setting initial balance of 1000
            ]);
        });

        // BusinessType::factory()->create([
        //     'name' => 'سوبرماركت',
        // ]);

        // BusinessType::factory()->create([
        //     'name' => 'صيدلية',
        // ]);
        Customer::factory()->create([
            'phone' => '01021153539',
            'phone_verified_at' => now(),
            'password' => 'password',
        ]);
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            ShieldSeeder::class,
            PendingOrderSeeder::class,
            SupplierSeeder::class,
            // ProductReportSeeder::class,
        ]);
    }
}
