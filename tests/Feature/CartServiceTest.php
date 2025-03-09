<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Warehouse;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->service = new CartService();
    $this->customer = Customer::factory()->create();
    $this->product = Product::factory()->create([
        'packet_to_piece' => 10,
        'packet_price' => 100,
        'piece_price' => 12,
    ]);
    $this->product2 = Product::factory()->create([
        'packet_to_piece' => 6,
        'packet_price' => 100,
        'piece_price' => 24,
    ]);

    StockItem::create([
        'warehouse_id' => 1,
        'product_id' => $this->product->id,
        'piece_quantity' => 100,
        'unavailable_quantity' => 0,
        'reserved_quantity' => 0,
        'release_date' => now(),
    ]);


    StockItem::create([
        'warehouse_id' => 1,
        'product_id' => $this->product2->id,
        'piece_quantity' => 100,
        'unavailable_quantity' => 0,
        'reserved_quantity' => 0,
        'release_date' => now(),
    ]);
});

test('can create cart for customer', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);

    expect($cart)->toBeInstanceOf(Cart::class)
        ->and($cart->customer_id)->toBe($this->customer->id);
});

test('can add item to cart', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);

    $item = $this->service->addItem($cart, $this->product, 1, 2);

    expect($item)->toBeInstanceOf(CartItem::class)
        ->and($item->packets_quantity)->toBe(1)
        ->and($item->piece_quantity)->toBe(2)
        ->and($item->total)->toBe('124.00'); // 1 packet (100) + 2 pieces (24)
});

test('throws exception when adding unavailable quantity', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);

    expect(fn() => $this->service->addItem($cart, $this->product, 20, 0))
        ->toThrow(\Exception::class, 'الكمية المطلوبة غير متوفرة');
});

test('can update item quantity', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);
    $item = $this->service->addItem($cart, $this->product, 1, 2);

    $updatedItem = $this->service->updateItemQuantity($item, 2, 3);

    expect($updatedItem->packets_quantity)->toBe(2)
        ->and($updatedItem->piece_quantity)->toBe(3)
        ->and($updatedItem->total)->toBe('236.00'); // 2 packets (200) + 3 pieces (36)
});

test('can delete item from cart', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);
    $item = $this->service->addItem($cart, $this->product, 1, 2);

    $this->service->deleteItem($item);

    expect($cart->fresh()->items()->count())->toBe(0)
        ->and($cart->fresh()->total)->toBe('0.00');
});

test('can empty cart', function () {
    $cart = $this->service->getOrCreateCart($this->customer->id);
    $this->service->addItem($cart, $this->product, 1, 2);
    $this->service->addItem($cart, $this->product2, 1, 2);

    $this->service->emptyCart($cart);

    expect($cart->fresh()->items()->count())->toBe(0)
        ->and($cart->fresh()->total)->toBe('0.00');
});
