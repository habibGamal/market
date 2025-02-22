<?php

use App\Models\Order;
use App\Models\Driver;
use App\Models\Product;
use App\Models\ReturnOrderItem;
use App\Models\User;
use App\Models\DriverTask;
use App\Services\DriverServices;
use App\Services\OrderServices;
use App\Enums\DriverStatus;
use App\Enums\ReturnOrderStatus;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->driver = Driver::factory()->create();
    $this->order = Order::factory()->create();
    $this->product = Product::factory()->create();
    $this->officer = User::factory()->create();
    $this->driverServices = app(DriverServices::class);
    $this->orderServices = app(OrderServices::class);
});

test('can assign orders to driver', function () {
    Auth::login($this->officer);
    $orders = Order::factory(3)->create();

    $this->driverServices->assignOrdersToDriver(collect($orders), $this->driver->id);

    foreach ($orders as $order) {
        $this->assertDatabaseHas('driver_tasks', [
            'driver_id' => $this->driver->id,
            'order_id' => $order->id,
            'driver_assisment_officer_id' => $this->officer->id,
            'status' => DriverStatus::PENDING,
        ]);
    }
});

test('can assign return orders to driver', function () {
    $returnOrders = ReturnOrderItem::factory(3)->create([
        'status' => ReturnOrderStatus::PENDING
    ]);

    $this->driverServices->assignReturnOrdersToDriver(collect($returnOrders), $this->driver->id);

    foreach ($returnOrders as $returnOrder) {
        $this->assertDatabaseHas('return_order_items', [
            'id' => $returnOrder->id,
            'driver_id' => $this->driver->id,
            'status' => ReturnOrderStatus::DRIVER_PICKUP,
        ]);
    }
});

test('can deliver order with partial quantities', function () {
    Auth::login($this->driver);

    $driverTask = DriverTask::factory()->create([
        'order_id' => $this->order->id,
        'driver_id' => $this->driver->id,
        'status' => DriverStatus::PENDING
    ]);

    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 10,
            'packet_price' => 100,
            'piece_quantity' => 20,
            'piece_price' => 10,
            'total' => 1200
        ]
    ];

    $orderItems = $this->orderServices->addOrderItems($this->order, $items);

    $receivedItems = [
        [
            'item_id' => $orderItems->first()->id,
            'packets_quantity' => 6, // 4 packets will be returned
            'piece_quantity' => 15,  // 5 pieces will be returned
        ]
    ];

    $this->driverServices->deliverOrder($this->order, $orderItems, $receivedItems);

    // Assert order status updated
    expect($this->order->fresh()->status)->toBe(OrderStatus::DELIVERED);

    // Assert driver task status updated
    expect($driverTask->fresh()->status)->toBe(DriverStatus::DONE);

    // Assert driver balance updated with net total
    expect((float) $this->driver->account->fresh()->balance)->toBe($this->order->netTotal);

    // Assert return items created
    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 4,
        'piece_quantity' => 5,
        'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
        'driver_id' => $this->driver->id,
        'return_reason' => 'لم يتم استلام الكمية كاملة',
    ]);

    // Assert driver returned products
    $this->assertDatabaseHas('driver_returned_products', [
        'driver_id' => $this->driver->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 4,
        'piece_quantity' => 5,
    ]);
});

test('can deliver order with full quantities', function () {
    Auth::login($this->driver);

    $driverTask = DriverTask::factory()->create([
        'order_id' => $this->order->id,
        'driver_id' => $this->driver->id
    ]);

    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 10,
            'packet_price' => 100,
            'piece_quantity' => 20,
            'piece_price' => 10,
            'total' => 1200
        ]
    ];

    $orderItems = $this->orderServices->addOrderItems($this->order, $items);

    $receivedItems = [
        [
            'item_id' => $orderItems->first()->id,
            'packets_quantity' => 10, // All packets received
            'piece_quantity' => 20,   // All pieces received
        ]
    ];

    $this->driverServices->deliverOrder($this->order, $orderItems, $receivedItems);

    // Assert order status updated
    expect($this->order->fresh()->status)->toBe(OrderStatus::DELIVERED);

    // Assert driver task status updated
    expect($driverTask->fresh()->status)->toBe(DriverStatus::DONE);

    // Assert driver balance updated with net total
    expect((float) $this->driver->account->fresh()->balance)->toBe($this->order->netTotal);

    // Assert no return items created
    $this->assertDatabaseMissing('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
    ]);

    $this->assertDatabaseMissing('driver_returned_products', [
        'driver_id' => $this->driver->id,
        'product_id' => $this->product->id,
    ]);
});

test('can deliver order with multiple products', function () {
    Auth::login($this->driver);

    $product2 = Product::factory()->create();
    $driverTask = DriverTask::factory()->create([
        'order_id' => $this->order->id,
        'driver_id' => $this->driver->id
    ]);

    $items = [
        [
            'product_id' => $this->product->id,
            'packets_quantity' => 10,
            'packet_price' => 100,
            'piece_quantity' => 20,
            'piece_price' => 10,
            'total' => 1200
        ],
        [
            'product_id' => $product2->id,
            'packets_quantity' => 5,
            'packet_price' => 200,
            'piece_quantity' => 10,
            'piece_price' => 20,
            'total' => 1200
        ]
    ];

    $orderItems = $this->orderServices->addOrderItems($this->order, $items);

    $receivedItems = [
        [
            'item_id' => $orderItems[0]->id,
            'packets_quantity' => 8,  // 2 packets will be returned
            'piece_quantity' => 15,   // 5 pieces will be returned
        ],
        [
            'item_id' => $orderItems[1]->id,
            'packets_quantity' => 3,  // 2 packets will be returned
            'piece_quantity' => 7,    // 3 pieces will be returned
        ]
    ];

    $this->driverServices->deliverOrder($this->order, $orderItems, $receivedItems);

    // Assert order status updated
    expect($this->order->fresh()->status)->toBe(OrderStatus::DELIVERED);

    // Assert driver task status updated
    expect($driverTask->fresh()->status)->toBe(DriverStatus::DONE);

    // Assert driver balance updated
    expect((float) $this->driver->account->fresh()->balance)->toBe($this->order->netTotal);

    // Check first product returns
    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
        'driver_id' => $this->driver->id
    ]);

    // Check second product returns
    $this->assertDatabaseHas('return_order_items', [
        'order_id' => $this->order->id,
        'product_id' => $product2->id,
        'packets_quantity' => 2,
        'piece_quantity' => 3,
        'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
        'driver_id' => $this->driver->id
    ]);

    // Check driver's returned products
    $this->assertDatabaseHas('driver_returned_products', [
        'driver_id' => $this->driver->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
    ]);

    $this->assertDatabaseHas('driver_returned_products', [
        'driver_id' => $this->driver->id,
        'product_id' => $product2->id,
        'packets_quantity' => 2,
        'piece_quantity' => 3,
    ]);
});

test('can mark return items as received from customer', function () {
    Auth::login($this->driver);

    $returnItems = ReturnOrderItem::factory(2)->create([
        'order_id' => $this->order->id,
        'status' => ReturnOrderStatus::PENDING,
    ]);

    $this->driverServices->markReturnItemsAsReceivedFromCustomer(collect($returnItems));

    foreach ($returnItems as $item) {
        $this->assertDatabaseHas('return_order_items', [
            'id' => $item->id,
            'status' => ReturnOrderStatus::RECEIVED_FROM_CUSTOMER,
        ]);

        $this->assertDatabaseHas('driver_returned_products', [
            'driver_id' => $this->driver->id,
            'product_id' => $item->product_id,
            'packets_quantity' => $item->packets_quantity,
            'piece_quantity' => $item->piece_quantity,
        ]);
    }
});
