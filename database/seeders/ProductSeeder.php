<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if we should use sample data or generate random data
        $useCsvData = true;

        if ($useCsvData) {
            $this->importFromCsv();
        } else {
            // Original seed method with factory data
            $this->seedWithFactoryData();
        }
    }

    /**
     * Import product data from the CSV file
     */
    private function importFromCsv(): void
    {
        // Path to the CSV file
        $csvPath = public_path('carrefouregypt4.csv');

        if (!File::exists($csvPath)) {
            $this->command->error('CSV file not found at: ' . $csvPath);
            return;
        }

        // Create directory for product images if it doesn't exist
        $imagesPath = public_path('images/products');
        if (!File::exists($imagesPath)) {
            File::makeDirectory($imagesPath, 0755, true);
        }

        // Read CSV data
        $csvFile = fopen($csvPath, 'r');

        // Skip header row
        $headers = fgetcsv($csvFile);

        $this->command->info('Importing products from CSV...');
        $progressBar = $this->command->getOutput()->createProgressBar(50); // Assuming 50 products from the CSV
        $progressBar->start();

        $count = 0;
        while (($data = fgetcsv($csvFile)) !== false && $count < 50) {
            // Map CSV columns to array keys
            $row = array_combine($headers, $data);

            // Extract brand name from JSON-like string in the CSV
            $brandName = $this->extractValue($row['Product Brand'], 'brandName');

            if (empty($brandName)) {
                $brandName = 'Unknown Brand';
            }

            // Find or create brand
            $brand = Brand::firstOrCreate(['name' => $brandName]);

            // Extract category name from JSON-like string in the CSV
            $categoryFullPath = $this->extractValue($row['Product Category'], 'productCategoriesHearchi');
            $categoryParts = explode('/', $categoryFullPath);
            $categoryName = trim(end($categoryParts));

            if (empty($categoryName)) {
                $categoryName = 'General';
            }

            // Find or create category
            $category = Category::firstOrCreate(['name' => $categoryName]);

            // Extract product barcode
            $barcode = $this->extractValue($row['Product Barcode'], 'barCodes');
            if (is_array($barcode) && !empty($barcode)) {
                $barcode = $barcode[0];
            } elseif (empty($barcode)) {
                $barcode = Str::random(13); // Generate random barcode if none available
            }

            // Extract price
            $priceString = $row['Product Price'];
            $price = (float) preg_replace('/[^0-9.]/', '', $priceString);

            // Download and save image
            $imageUrl = $row['Product Image-src'];
            $imagePath = null;

            if (!empty($imageUrl)) {
                $imagePath = $this->downloadImage($imageUrl, $barcode);
            }

            // Create product
            $product = Product::factory()->create([
                'name' => $row['Product Name'],
                'barcode' => $barcode,
                'packet_price' => $price,
                'piece_price' => $price,
                'packet_to_piece' => 1,
                'image' => $imagePath,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
            ]);

            $progressBar->advance();
            $count++;
        }

        fclose($csvFile);
        $progressBar->finish();
        $this->command->info("\nImported {$count} products from CSV");
    }

    /**
     * Download an image from URL and save it locally
     */
    private function downloadImage(string $url, string $barcode): ?string
    {
        try {
            $filename = Str::slug($barcode) . '-' . time() . '.jpg';
            $savePath = 'products/' . $filename;

            // Get image content from URL
            $response = Http::timeout(10)
                ->withHeaders(
                    [
                        'User-Agent' => 'PostmanRuntime/7.43.0',
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Accept-Language' => 'en-US,en;q=0.9,ar-EG;q=0.8,ar;q=0.7'
                    ]
                )->get($url);
            if ($response->successful()) {
                // Save image to public directory
                Storage::disk('public')->put($savePath, $response->body());
                return $savePath;
            }
        } catch (\Exception $e) {
            report($e);
        }

        return null;
    }

    /**
     * Extract values from JSON-like strings in the CSV
     */
    private function extractValue(string $jsonString, string $key)
    {
        // Try to parse JSON-like string
        preg_match('/"' . $key . '"\s*:\s*"([^"]+)"/', $jsonString, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        // For array values like barCodes
        if ($key === 'barCodes') {
            preg_match('/"' . $key . '"\s*:\s*\[\s*"([^"]+)"/', $jsonString, $matches);
            if (isset($matches[1])) {
                return [$matches[1]];
            }
        }

        return null;
    }

    /**
     * Seed with factory generated data
     */
    private function seedWithFactoryData(): void
    {
        \App\Models\Brand::factory()->count(10)->create();
        \App\Models\Category::factory()->count(5)->create();

        $brands = \App\Models\Brand::all();
        $catgories = \App\Models\Category::all();

        \App\Models\Product::factory()
            ->count(100)
            ->make()
            ->each(function ($product) use ($brands, $catgories) {
                $product->brand_id = $brands->random()->id;
                $product->category_id = $catgories->random()->id;
                $product->save();
            });
    }
}
