<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\IssueNote;
use App\Models\IssueNoteItem;
use App\Models\DriverTask;
use App\Enums\OrderStatus;
use App\Enums\DriverStatus;
use App\Enums\InvoiceStatus;
use App\Enums\IssueNoteType;
use App\Services\IssueNoteServices;
use App\Services\StockServices;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'packet_to_piece' => 10
    ]);

    $this->issueNote = IssueNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'note_type' => IssueNoteType::ORDERS
    ]);
});

test('can create issue note from orders', function () {
    // Mock StockServices
    $stockServices = $this->mock(StockServices::class);
    $stockServices->shouldReceive('getReservedQuantities')
        ->once()
        ->andReturn([
            now()->toDateString() => 25 // Total pieces (2 packets * 10 pieces/packet + 5 pieces)
        ]);

    $order = Order::factory()->create();

    // Create order item directly
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'packet_price' => 100,
        'piece_price' => 10,
    ]);

    // Create IssueNoteServices instance
    $issueNoteServices = app(IssueNoteServices::class);

    // Act
    $issueNoteServices->fromOrders($this->issueNote, collect([$order]));

    // Assert - expecting 2 full packets and 5 loose pieces
    $this->assertDatabaseHas('issue_note_items', [
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'release_date' => now()->toDateString(),
    ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => OrderStatus::PREPARING,
        'issue_note_id' => $this->issueNote->id
    ]);
});

test('can create issue note from multiple orders with same products', function () {
    // Mock StockServices
    $stockServices = $this->mock(StockServices::class);
    $stockServices->shouldReceive('getReservedQuantities')
        ->once()
        ->andReturn([
            now()->toDateString() => 50 // Total pieces: (2*10 + 5) * 2 orders
        ]);

    // Create orders and items separately
    $orders = Order::factory(2)->create();
    foreach ($orders as $order) {
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'packets_quantity' => 2,
            'piece_quantity' => 5,
            'packet_price' => 100,
            'piece_price' => 10,
        ]);
    }

    // Act
    app(IssueNoteServices::class)->fromOrders($this->issueNote, $orders);

    // Assert - expecting 5 full packets (50 pieces / 10 pieces per packet)
    $this->assertDatabaseHas('issue_note_items', [
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'piece_quantity' => 0,
        'release_date' => now()->toDateString(),
    ]);

    foreach ($orders as $order) {
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PREPARING,
            'issue_note_id' => $this->issueNote->id
        ]);
    }
});

test('can create issue note with multiple release dates', function () {
    // Mock StockServices
    $stockServices = $this->mock(StockServices::class);
    $stockServices->shouldReceive('getReservedQuantities')
        ->once()
        ->andReturn([
            now()->toDateString() => 15, // 1 packet + 5 pieces
            now()->addDays(1)->toDateString() => 10, // 1 packet exactly
        ]);

    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'packet_price' => 100,
        'piece_price' => 10,
    ]);

    // Act
    app(IssueNoteServices::class)->fromOrders($this->issueNote, collect([$order]));

    // Assert - should create items with correct packet/piece quantities for each date
    $this->assertDatabaseHas('issue_note_items', [
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 1,
        'piece_quantity' => 5,
        'release_date' => now()->toDateString(),
    ]);

    $this->assertDatabaseHas('issue_note_items', [
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 1,
        'piece_quantity' => 0,
        'release_date' => now()->addDays(1)->toDateString(),
    ]);
});

test('can close issue note and update orders and tasks status', function () {
    $order = Order::factory()->create(['issue_note_id' => $this->issueNote->id]);
    $driverTask = DriverTask::factory()->create(['order_id' => $order->id]);

    // Create issue note item
    $issueNoteItem = IssueNoteItem::factory()->create([
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'piece_quantity' => 3,
        'release_date' => now(),
    ]);

    // Mock StockServices
    $this->mock(StockServices::class, function ($mock) use ($issueNoteItem) {
        $mock->shouldReceive('removeFromReserve')
            ->once()
            ->withArgs(function($product, $quantities) use ($issueNoteItem) {
                return $product->is($this->product) &&
                       $quantities[$issueNoteItem->release_date->toDateString()] === (5 * 10) + 3;
            });
    });

    // Act
    app(IssueNoteServices::class)->closeOrdersIssueNote($this->issueNote);

    // Assert
    expect($order->fresh()->status)->toBe(OrderStatus::OUT_FOR_DELIVERY)
        ->and($driverTask->fresh()->status)->toBe(DriverStatus::RECEIVED);
});

test('can close issue note with multiple orders', function () {
    $orders = Order::factory(2)->create(['issue_note_id' => $this->issueNote->id]);
    $driverTasks = $orders->map(fn($order) => DriverTask::factory()->create(['order_id' => $order->id]));

    $issueNoteItem = IssueNoteItem::factory()->create([
        'issue_note_id' => $this->issueNote->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 5,
        'piece_quantity' => 3,
        'release_date' => now(),
    ]);

    // Mock StockServices
    $this->mock(StockServices::class, function ($mock) use ($issueNoteItem) {
        $mock->shouldReceive('removeFromReserve')
            ->once()
            ->withArgs(function($product, $quantities) use ($issueNoteItem) {
                return $product->is($this->product) &&
                       $quantities[$issueNoteItem->release_date->toDateString()] === (5 * 10) + 3;
            });
    });

    // Act
    app(IssueNoteServices::class)->closeOrdersIssueNote($this->issueNote);

    // Assert all orders and tasks are updated
    foreach ($orders as $order) {
        expect($order->fresh()->status)->toBe(OrderStatus::OUT_FOR_DELIVERY);
    }

    foreach ($driverTasks as $task) {
        expect($task->fresh()->status)->toBe(DriverStatus::RECEIVED);
    }
});
