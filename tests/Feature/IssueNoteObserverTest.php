<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\IssueNote;
use App\Models\IssueNoteItem;
use App\Models\DriverTask;
use App\Enums\OrderStatus;
use App\Enums\DriverStatus;
use App\Enums\InvoiceStatus;
use App\Enums\IssueNoteType;
use App\Services\StockServices;

test('observer triggers closeOrdersIssueNote when status changes to closed', function () {
    // Create issue note with associated order and items
    $issueNote = IssueNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'note_type' => IssueNoteType::ORDERS
    ]);

    $order = Order::factory()->create(['issue_note_id' => $issueNote->id]);
    $driverTask = DriverTask::factory()->create(['order_id' => $order->id]);

    $product = Product::factory()->create(['packet_to_piece' => 10]);
    $issueNoteItem = IssueNoteItem::factory()->create([
        'issue_note_id' => $issueNote->id,
        'product_id' => $product->id,
        'packets_quantity' => 5,
        'piece_quantity' => 3,
        'release_date' => now(),
    ]);

    // Mock StockServices to verify removeFromReserve is called
    $this->mock(StockServices::class, function ($mock) use ($product, $issueNoteItem) {
        $mock->shouldReceive('removeFromReserve')
            ->once()
            ->withArgs(function($actualProduct, $quantities) use ($product, $issueNoteItem) {
                return $actualProduct->is($product) &&
                       $quantities[$issueNoteItem->release_date->toDateString()] === (5 * 10) + 3;
            });
    });

    // Update issue note status to closed - should trigger observer
    $issueNote->update(['status' => InvoiceStatus::CLOSED]);

    // Verify order and task statuses were updated
    expect($order->fresh()->status)->toBe(OrderStatus::OUT_FOR_DELIVERY)
        ->and($driverTask->fresh()->status)->toBe(DriverStatus::RECEIVED)
        ->and($issueNote->fresh()->status)->toBe(InvoiceStatus::CLOSED);
});
