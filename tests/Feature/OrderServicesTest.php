<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderServices;
use App\Services\StockServices;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->order = Order::factory()->create();
    $this->product = Product::factory()->create();
    $this->orderService = app(OrderServices::class);
    $this->user = User::factory()->create();
    $this->stockService = app(StockServices::class);

    // Add initial stock for tests
    $this->stockService->addTo($this->product, [
        now()->format('Y-m-d') => 100 // Add enough stock for tests
    ]);
});

test('can add order items', function () {
    $items = [
        [
            'product_id' => $this->product->id,
            'piece_quantity' => 5,
            'piece_price' => 100,
            'total' => 500
        ]
    ];

    $createdItems = $this->orderService->addOrderItems($this->order, $items);

    expect($createdItems)->toHaveCount(1);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'piece_quantity' => 5,
        'piece_price' => 100,
        'total' => 500
    ]);


    expect($this->order->fresh()->total)->toBe('500.00');
});

test('can add order items with both packets and pieces', function () {
    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 5,
            'piece_price' => 100,
            'total' => 1500 // (2 * 500) + (5 * 100)
        ],
    ];

    $createdItems = $this->orderService->addOrderItems($this->order, $items);

    expect($createdItems)->toHaveCount(1);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 5,
        'piece_price' => 100,
        'total' => 1500
    ]);

    expect($this->order->fresh()->total)->toBe('1500.00');
});

test('can add order items with only packets', function () {
    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 3,
            'packet_price' => 500,
            'piece_quantity' => 0,
            'piece_price' => 0,
            'total' => 1500 // 3 * 500
        ]
    ];

    $createdItems = $this->orderService->addOrderItems($this->order, $items);

    expect($createdItems)->toHaveCount(1);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 3,
        'packet_price' => 500,
        'piece_quantity' => 0,
        'piece_price' => 0,
        'total' => 1500
    ]);

    expect($this->order->fresh()->total)->toBe('1500.00');
});

test('can add order items with only pieces', function () {
    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 0,
            'packet_price' => 0,
            'piece_quantity' => 10,
            'piece_price' => 100,
            'total' => 1000 // 10 * 100
        ]
    ];

    $createdItems = $this->orderService->addOrderItems($this->order, $items);

    expect($createdItems)->toHaveCount(1);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 1000
    ]);

    expect($this->order->fresh()->total)->toBe('1000.00');
});

test('can cancel order items', function () {
    Auth::login($this->user);

    // Create initial order item
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 1000
    ]);

    $itemsToCancel = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 1,
            'packet_price' => 500,
            'piece_quantity' => 4,
            'piece_price' => 100,
            'notes' => 'Test cancellation'
        ]
    ];

    $this->orderService->cancelledItems($this->order, $itemsToCancel);

    // Check that original order item was updated
    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'piece_quantity' => 6, // 10 - 4
    ]);

    // Check that cancelled item was created
    $this->assertDatabaseHas('cancelled_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'piece_quantity' => 4,
        'piece_price' => 100,
        'officer_id' => $this->user->id,
        'notes' => 'Test cancellation'
    ]);
});

test('can cancel order items with both packets and pieces', function () {
    Auth::login($this->user);

    // Create initial order item with both packets and pieces
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'packet_price' => 500,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 3500 // (5 * 500) + (10 * 100)
    ]);

    $itemsToCancel = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 4,
            'piece_price' => 100,
            'notes' => 'Test cancellation'
        ]
    ];

    $this->orderService->cancelledItems($this->order, $itemsToCancel);

    // Check that original order item was updated correctly
    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 3, // 5 - 2
        'piece_quantity' => 6, // 10 - 4
    ]);

    // Check that cancelled item was created with correct quantities
    $this->assertDatabaseHas('cancelled_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 4,
        'piece_price' => 100,
        'officer_id' => $this->user->id,
        'notes' => 'Test cancellation'
    ]);
});

test('can cancel order items with only packets', function () {
    Auth::login($this->user);

    // Create initial order item with only packets
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'packet_price' => 500,
        'piece_quantity' => 0,
        'piece_price' => 0,
        'total' => 2500 // 5 * 500
    ]);

    $itemsToCancel = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 0,
            'piece_price' => 0,
            'notes' => 'Test packet cancellation'
        ]
    ];

    $this->orderService->cancelledItems($this->order, $itemsToCancel);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 3, // 5 - 2
        'piece_quantity' => 0,
    ]);

    $this->assertDatabaseHas('cancelled_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 0,
        'piece_price' => 0,
        'notes' => 'Test packet cancellation'
    ]);
});

test('can cancel order items with only pieces', function () {
    Auth::login($this->user);

    // Create initial order item with only pieces
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 1000 // 10 * 100
    ]);

    $itemsToCancel = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 0,
            'packet_price' => 0,
            'piece_quantity' => 4,
            'piece_price' => 100,
            'notes' => 'Test piece cancellation'
        ]
    ];

    $this->orderService->cancelledItems($this->order, $itemsToCancel);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 0,
        'piece_quantity' => 6, // 10 - 4
    ]);

    $this->assertDatabaseHas('cancelled_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 4,
        'piece_price' => 100,
        'notes' => 'Test piece cancellation'
    ]);
});

test('cannot cancel more items than available', function () {
    Auth::login($this->user);

    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 5,
        'piece_price' => 100,
        'total' => 1500
    ]);

    $itemsToCancel = [[
        'order_item' => $orderItem,
        'product_id' => $this->product->id,
        'packets_quantity' => 3,
        'packet_price' => 500,
        'piece_quantity' => 6,
        'piece_price' => 100,
        'notes' => 'Test invalid cancellation'
    ]];

    expect(fn() => $this->orderService->cancelledItems($this->order, $itemsToCancel))
        ->toThrow('لا يمكن إلغاء كمية أكبر من المتاحة');
});

test('can return order items', function () {
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 1000
    ]);

    $itemsToReturn = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 0,
            'packet_price' => 0,
            'piece_quantity' => 3,
            'piece_price' => 100,
            'notes' => 'Test return',
            'return_reason' => 'Damaged product'
        ]
    ];

    $this->orderService->returnItems($this->order, $itemsToReturn);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'piece_quantity' => 10,
    ]);

    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 3,
        'piece_price' => 100,
        'notes' => 'Test return',
        'return_reason' => 'Damaged product',
        'status' => 'pending'
    ]);
});

test('can return order items with both packets and pieces', function () {
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'packet_price' => 500,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 3500
    ]);

    $itemsToReturn = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 3,
            'piece_price' => 100,
            'notes' => 'Test return',
            'return_reason' => 'Damaged products'
        ]
    ];

    $this->orderService->returnItems($this->order, $itemsToReturn);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 5,
        'piece_quantity' => 10,
    ]);

    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 3,
        'piece_price' => 100,
        'notes' => 'Test return',
        'return_reason' => 'Damaged products',
        'status' => 'pending'
    ]);
});

test('can return order items with only packets', function () {
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'packet_price' => 500,
        'piece_quantity' => 0,
        'piece_price' => 0,
        'total' => 2500
    ]);

    $itemsToReturn = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 0,
            'piece_price' => 0,
            'notes' => 'Test packet return',
            'return_reason' => 'Wrong packet size'
        ]
    ];

    $this->orderService->returnItems($this->order, $itemsToReturn);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 5,
        'piece_quantity' => 0,
    ]);

    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 0,
        'piece_price' => 0,
        'notes' => 'Test packet return',
        'return_reason' => 'Wrong packet size',
        'status' => 'pending'
    ]);
});

test('can return order items with only pieces', function () {
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'packet_price' => 0,
        'piece_quantity' => 10,
        'piece_price' => 100,
        'total' => 1000
    ]);

    $itemsToReturn = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 0,
            'packet_price' => 0,
            'piece_quantity' => 4,
            'piece_price' => 100,
            'notes' => 'Test piece return',
            'return_reason' => 'Damaged pieces'
        ]
    ];

    $this->orderService->returnItems($this->order, $itemsToReturn);

    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'packets_quantity' => 0,
        'piece_quantity' => 10,
    ]);

    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 0,
        'piece_price' => 100,
        'piece_quantity' => 4,
        'notes' => 'Test piece return',
        'return_reason' => 'Damaged pieces',
        'status' => 'pending'
    ]);
});

test('cannot return more items than ordered', function () {
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 5,
        'piece_price' => 100,
        'total' => 1500
    ]);

    $itemsToReturn = [[
        'order_item' => $orderItem,
        'product_id' => $this->product->id,
        'packets_quantity' => 3,
        'packet_price' => 500,
        'piece_quantity' => 6,
        'piece_price' => 100,
        'notes' => 'Test invalid return',
        'return_reason' => 'Invalid test'
    ]];

    expect(fn() => $this->orderService->returnItems($this->order, $itemsToReturn))
        ->toThrow('لا يمكن إرجاع كمية أكبر من المطلوبة');
});

test('can remove return items', function () {
    $returnItem = $this->order->returnItems()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 3,
        'piece_price' => 100,
        'return_reason' => 'Test return',
        'notes' => 'Test notes',
        'status' => 'pending'
    ]);

    $this->orderService->removeReturnItems($this->order, collect([$returnItem]));

    $this->assertDatabaseMissing('return_order_items', [
        'id' => $returnItem->id
    ]);
});

test('reserves stock when adding order items', function () {
    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'packet_price' => 500,
            'piece_quantity' => 5,
            'piece_price' => 100,
            'total' => 1500
        ]
    ];

    // Calculate total pieces that should be reserved
    $totalPieces = ($items[0]['packets_quantity'] * $this->product->packet_to_piece) + $items[0]['piece_quantity'];

    // Add items and verify stock reservation
    $this->orderService->addOrderItems($this->order, $items);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $this->product->id,
        'reserved_quantity' => $totalPieces
    ]);
});

test('unreserves stock when cancelling order items', function () {
    Auth::login($this->user);

    // Create initial order item
    $orderItem = $this->order->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_price' => 500,
        'piece_quantity' => 5,
        'piece_price' => 100,
        'total' => 1500
    ]);

    // Reserve initial stock
    $initialTotalPieces = ($orderItem->packets_quantity * $this->product->packet_to_piece) + $orderItem->piece_quantity;
    $this->stockService->reserve($this->product, $initialTotalPieces);

    $itemsToCancel = [
        [
            'order_item' => $orderItem,
            'product_id' => $this->product->id,
            'packets_quantity' => 1,
            'packet_price' => 500,
            'piece_quantity' => 3,
            'piece_price' => 100,
            'notes' => 'Test cancellation'
        ]
    ];

    // Cancel items and verify stock unreservation
    $this->orderService->cancelledItems($this->order, $itemsToCancel);

    // Calculate pieces that should be unreserved
    $cancelledPieces = ($itemsToCancel[0]['packets_quantity'] * $this->product->packet_to_piece) + $itemsToCancel[0]['piece_quantity'];
    $remainingReserved = $initialTotalPieces - $cancelledPieces;

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $this->product->id,
        'reserved_quantity' => $remainingReserved
    ]);
});
