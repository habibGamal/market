<?php

use App\Models\Offer;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\OfferService;

beforeEach(function () {
    $this->offerService = app(OfferService::class);
});

test('returns zero discount when no active offers exist', function () {
    $order = Order::factory()->create();

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(0)
        ->and($result['applied_offers'])->toBeEmpty();
});

test('returns zero discount when no offers match conditions', function () {
    // Create an active offer with business type condition
    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'in_business_type' => [999] // Non-existent business type
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $order = Order::factory()->create();

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(0)
        ->and($result['applied_offers'])->toBeEmpty();
});

test('applies percentage discount correctly', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1)
        ->and($result['applied_offers']->first()->id)->toBe($offer->id);
});

test('applies fixed discount correctly', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [],
            'discount' => [
                'type' => 'fixed',
                'value' => 50
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(50.0)
        ->and($result['applied_offers'])->toHaveCount(1)
        ->and($result['applied_offers']->first()->id)->toBe($offer->id);
});

test('applies business type condition correctly', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'in_business_type' => [$customer->business_type_id]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies location conditions correctly', function () {
    $customer = Customer::factory()->create();

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'in_gov' => [$customer->gov_id],
                'in_cities' => [$customer->city_id],
                'in_areas' => [$customer->area_id]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies minimum requirements correctly', function () {
    $customer = Customer::factory()->create(['rating_points' => 100]);
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    $orderItem = $order->items()->createQuietly([
        'product_id' => Product::factory()->create()->id,
        'packets_quantity' => 5,
        'total' => 1000
    ]);

    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'min_total_packets' => 5,
                'min_customer_points' => 100,
                'min_total_order' => 1000
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies category conditions correctly', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $order = Order::factory()->create(['total' => 1000]);
    $orderItem = $order->items()->createQuietly([
        'product_id' => $product->id,
        'total' => 1000
    ]);

    // Test general category strategy
    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'categories' => [
                    'strategy' => 'general',
                    'general' => [
                        'number_of_diff_categories' => 1,
                        'min_value' => 1000
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);

    // Test specific category strategy
    $offer->update([
        'instructions' => [
            'conditions' => [
                'categories' => [
                    'strategy' => 'specific',
                    'specific' => [
                        $category->id => [
                            'min_value' => 1000
                        ]
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies brand conditions correctly', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);

    $order = Order::factory()->create(['total' => 1000]);
    $orderItem = $order->items()->createQuietly([
        'product_id' => $product->id,
        'total' => 1000
    ]);

    // Test general brand strategy
    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'brands' => [
                    'strategy' => 'general',
                    'general' => [
                        'number_of_diff_brands' => 1,
                        'min_value' => 1000
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);
    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);

    // Test specific brand strategy
    $offer->update([
        'instructions' => [
            'conditions' => [
                'brands' => [
                    'strategy' => 'specific',
                    'specific' => [
                        $brand->id => [
                            'min_value' => 1000
                        ]
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies product conditions correctly', function () {
    $product = Product::factory()->create();

    $order = Order::factory()->create(['total' => 1000]);
    $orderItem = $order->items()->createQuietly([
        'product_id' => $product->id,
        'total' => 1000
    ]);

    // Test general product strategy
    $offer = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [
                'products' => [
                    'strategy' => 'general',
                    'general' => [
                        'number_of_diff_products' => 1,
                        'min_value' => 1000
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);

    // Test specific product strategy
    $offer->update([
        'instructions' => [
            'conditions' => [
                'products' => [
                    'strategy' => 'specific',
                    'specific' => [
                        $product->id => [
                            'min_value' => 1000
                        ]
                    ]
                ]
            ],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(100.0)
        ->and($result['applied_offers'])->toHaveCount(1);
});

test('applies multiple offers cumulatively', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'total' => 1000
    ]);

    // Create two different offers
    $offer1 = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [],
            'discount' => [
                'type' => 'percent',
                'value' => 10
            ]
        ]
    ]);

    $offer2 = Offer::factory()->create([
        'is_active' => true,
        'start_at' => now()->subDay(),
        'end_at' => now()->addDay(),
        'instructions' => [
            'conditions' => [],
            'discount' => [
                'type' => 'fixed',
                'value' => 50
            ]
        ]
    ]);

    $result = $this->offerService->calculateOrderDiscount($order);

    expect($result['discount'])->toBe(150.0) // 100 (10%) + 50 (fixed)
        ->and($result['applied_offers'])->toHaveCount(2);
});
