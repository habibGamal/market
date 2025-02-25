<?php

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Models\Driver;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\ReceiptNote;
use App\Models\StockItem;
use App\Models\User;
use App\Services\ReceiptNoteServices;
use App\Services\StockServices;
use App\Services\ProductManageCostServices;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'packet_price' => 500,
        'packet_to_piece' => 10,
        'packet_cost' => 400, // Added initial cost
        'name' => 'Test Product'
    ]);
    $this->receiptNoteServices = app(ReceiptNoteServices::class);
    $this->user = User::factory()->create(['name' => 'Test User']);
    Auth::login($this->user);
});

test('can create receipt note from purchase invoice', function () {
    $purchaseInvoice = PurchaseInvoice::factory()->create([
        'total' => 1000
    ]);

    $purchaseInvoice->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_cost' => 500,
        'total' => 1000
    ]);
    $receipt = $this->receiptNoteServices->createFromPurchaseInvoice($purchaseInvoice);

    expect($receipt->note_type)->toBe(ReceiptNoteType::PURCHASES)
        ->and($receipt->status)->toBe(InvoiceStatus::DRAFT)
        ->and($receipt->officer_id)->toBe($this->user->id);

    $this->assertDatabaseHas('receipt_note_items', [
        'receipt_note_id' => $receipt->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_cost' => 500,
        'piece_quantity' => 0,
        'total' => 1000
    ]);

    expect($receipt->items()->first()->release_dates)->toBeArray()
        ->and($receipt->items()->first()->release_dates[0]['piece_quantity'])->toBe(20); // 2 packets * 10 pieces per packet
});

test('can create receipt note from driver returns', function () {
    $driver = Driver::factory()->create();

    // Setup returned products for driver
    $driver->returnedProducts()->attach($this->product->id, [
        'packets_quantity' => 2,
        'piece_quantity' => 5
    ]);

    $receipt = $this->receiptNoteServices->createFromDriverReturns($driver);

    expect($receipt->note_type)->toBe(ReceiptNoteType::RETURN_ORDERS)
        ->and($receipt->status)->toBe(InvoiceStatus::DRAFT)
        ->and($receipt->officer_id)->toBe($this->user->id);

    $this->assertDatabaseHas('receipt_note_items', [
        'receipt_note_id' => $receipt->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'packet_cost' => 500
    ]);

    expect($receipt->items()->first()->release_dates)->toBeArray()
        ->and($receipt->items()->first()->release_dates[0]['piece_quantity'])->toBe(25); // (2 packets * 10 pieces) + 5 pieces

    $this->assertDatabaseHas('driver_receipts', [
        'driver_id' => $driver->id,
        'receipt_note_id' => $receipt->id
    ]);
});

test('can remove quantities from driver products', function () {
    $driver = Driver::factory()->create();

    // Setup initial returned products
    $driver->returnedProducts()->attach($this->product->id, [
        'packets_quantity' => 5,
        'piece_quantity' => 10
    ]);

    $receipt = ReceiptNote::factory()->create([
        'note_type' => ReceiptNoteType::RETURN_ORDERS,
        'total' => 0,
        'officer_id' => $this->user->id
    ]);

    $receipt->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 4,
        'packet_cost' => 500,
        'total' => 1000,
        'release_dates' => [
            [
                'piece_quantity' => 25, // (2 packets * 10 pieces) + 5 pieces
                'release_date' => now()->format('Y-m-d'),
            ]
        ]
    ]);

    $driver->receipts()->attach($receipt);

    $this->receiptNoteServices->removeQuantitiesFromDriverProducts($receipt);

    $this->assertDatabaseHas('driver_returned_products', [
        'driver_id' => $driver->id,
        'product_id' => $this->product->id,
        'packets_quantity' => 3, // 5 - 2
        'piece_quantity' => 6 // 10 - 4
    ]);
});

test('cannot remove more quantities than available from driver products', function () {
    $driver = Driver::factory()->create();

    // Setup initial returned products
    $driver->returnedProducts()->attach($this->product->id, [
        'packets_quantity' => 2,
        'piece_quantity' => 5
    ]);

    $receipt = ReceiptNote::factory()->create([
        'note_type' => ReceiptNoteType::RETURN_ORDERS,
        'total' => 0,
        'officer_id' => $this->user->id
    ]);

    $receipt->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 3, // More than available
        'piece_quantity' => 6, // More than available
        'packet_cost' => 500,
        'total' => 1500,
        'release_dates' => [
            [
                'piece_quantity' => 25, // (2 packets * 10 pieces) + 5 pieces
                'release_date' => now()->format('Y-m-d'),
            ]
        ]
    ]);

    $driver->receipts()->attach($receipt);

    expect(fn() => $this->receiptNoteServices->removeQuantitiesFromDriverProducts($receipt))
        ->toThrow('لا يمكن أن تكون الكمية المرتجعة أكبر من الكمية المتوفرة');
});

test('can remove all quantities and detach product', function () {
    $driver = Driver::factory()->create();

    // Setup initial returned products
    $driver->returnedProducts()->attach($this->product->id, [
        'packets_quantity' => 2,
        'piece_quantity' => 5
    ]);

    $receipt = ReceiptNote::factory()->create([
        'note_type' => ReceiptNoteType::RETURN_ORDERS,
        'total' => 0,
        'officer_id' => $this->user->id
    ]);

    $receipt->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'piece_quantity' => 5,
        'packet_cost' => 500,
        'total' => 1000,
        'release_dates' => [
            [
                'piece_quantity' => 25, // (2 packets * 10 pieces) + 5 pieces
                'release_date' => now()->format('Y-m-d'),
            ]
        ]
    ]);

    $driver->receipts()->attach($receipt);

    $this->receiptNoteServices->removeQuantitiesFromDriverProducts($receipt);

    $this->assertDatabaseMissing('driver_returned_products', [
        'driver_id' => $driver->id,
        'product_id' => $this->product->id
    ]);
});

test('can add stock from purchase receipt note', function () {

    $purchaseInvoice = PurchaseInvoice::factory()->create([
        'total' => 1000
    ]);

    $purchaseInvoice->items()->create([
        'product_id' => $this->product->id,
        'packets_quantity' => 2,
        'packet_cost' => 500,
        'total' => 1000
    ]);

    $receipt = $this->receiptNoteServices->createFromPurchaseInvoice($purchaseInvoice);
    // dd($receipt->items()->get());
    $this->receiptNoteServices->toStock($receipt);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $this->product->id,
        'piece_quantity' => 20 // 2 packets * 10 pieces per packet
    ]);

    $this->product->refresh();
    expect($this->product->packet_cost)->toBe('500.00'); // Should update to new cost
});

test('can add stock from driver return receipt note', function () {
    $driver = Driver::factory()->create();

    $driver->returnedProducts()->attach($this->product->id, [
        'packets_quantity' => 2,
        'piece_quantity' => 5
    ]);

    $receipt = $this->receiptNoteServices->createFromDriverReturns($driver);
    $this->receiptNoteServices->toStock($receipt);

    $this->assertDatabaseHas('stock_items', [
        'product_id' => $this->product->id,
        'piece_quantity' => 25 // (2 packets * 10 pieces) + 5 pieces
    ]);

    $this->product->refresh();
    expect($this->product->packet_cost)->toBe('500.00'); // Should update to new cost from the receipt
});


