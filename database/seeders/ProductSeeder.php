<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        \App\Models\Brand::factory()->count(100)->create();
        \App\Models\Category::factory()->count(5)->create();

        $brands = \App\Models\Brand::all();
        $catgories = \App\Models\Category::all();

        \App\Models\Product::factory([
            'brand_id' => $brands->random()->id,
            'category_id' => $catgories->random()->id,
        ])
            ->count(1000)
            ->make()
            ->each(function ($product) use ($brands, $catgories) {
                $product->brand_id = $brands->random()->id;
                $product->category_id = $catgories->random()->id;
                $product->save();
            });
    }
}
