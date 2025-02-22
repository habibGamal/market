<?php

use App\Models\Product;
use App\Services\StockServices;
use Illuminate\Support\Facades\Concurrency;

test('add to stock', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'piece_quantity' => 10,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'piece_quantity' => 20,
    ]);

    $stockService->addTo($product, [
        '2025-02-12' => 5,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'piece_quantity' => 15,
    ]);
});


test('reserve stock', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->reserve($product, 15);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'reserved_quantity' => 10,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'reserved_quantity' => 5,
    ]);
});


test('unavailable stock', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->unavailable($product, [
        '2025-02-12' => 5,
        '2025-02-13' => 10,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'unavailable_quantity' => 5,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'unavailable_quantity' => 10,
    ]);
});


test('undo reserve stock', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->reserve($product, 15);
    $stockService->undoReserve($product, 10);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'reserved_quantity' => 0,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'reserved_quantity' => 5,
    ]);
});


test('remove from reserve', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->reserve($product, 15);
    $stockService->removeFromReserve($product, [
        '2025-02-12' => 10,
        '2025-02-13' => 5,
    ]);

    $this->assertDatabaseMissing('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'piece_quantity' => 15,
        'reserved_quantity' => 0,
    ]);
});


test('remove from unavailable', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->unavailable($product, [
        '2025-02-12' => 5,
        '2025-02-13' => 10,
    ]);
    $stockService->removeFromUnavailable($product, [
        '2025-02-12' => 5,
        '2025-02-13' => 10,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-12',
        'piece_quantity' => 5,
        'unavailable_quantity' => 0,
    ]);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $product->id,
        'release_date' => '2025-02-13',
        'piece_quantity' => 10,
        'unavailable_quantity' => 0,
    ]);
});

test('get reserved quantities', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->reserve($product, 15);

    $reservedQuantities = $stockService->getReservedQuantities($product, 15);

    expect($reservedQuantities)->toBe([
        '2025-02-12' => 10,
        '2025-02-13' => 5
    ]);
});

test('get reserved quantities throws exception if not enough quantity', function () {
    $product = Product::factory()->create();
    $quantities = [
        '2025-02-12' => 10,
        '2025-02-13' => 20,
    ];

    $stockService = new StockServices();
    $stockService->addTo($product, $quantities);
    $stockService->reserve($product, 15);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('الكمية المطلوبة غير متوفرة');

    $stockService->getReservedQuantities($product, 50);
});
