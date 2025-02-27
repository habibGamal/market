<?php

use App\Enums\IssueNoteType;
use App\Enums\ReceiptNoteType;
use App\Models\User;
use App\Models\WorkDay;
use App\Models\AccountantIssueNote;
use App\Models\AccountantReceiptNote;
use App\Models\Driver;
use App\Models\IssueNote;
use App\Models\ReceiptNote;
use App\Models\Expense;
use App\Services\WorkDayService;
use Illuminate\Support\Carbon;

test('get today work day creates new if not exists', function () {
    $service = new WorkDayService();
    $workDay = $service->getToday();

    expect($workDay)->toBeInstanceOf(WorkDay::class)
        ->and($workDay->day->toDateString())->toBe(Carbon::today()->toDateString())
        ->and($workDay->start_day)->toBe('0.00')
        ->and($workDay->total_purchase)->toBe('0.00')
        ->and($workDay->total_sales)->toBe('0.00')
        ->and($workDay->total_expenses)->toBe('0.00')
        ->and($workDay->total_purchase_returnes)->toBe('0.00')
        ->and($workDay->total_day)->toBe('0.00');
});

test('get today work day uses previous day total', function () {
    // Create previous day with some total
    WorkDay::create([
        'day' => Carbon::yesterday(),
        'total_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->getToday();

    expect($workDay->start_day)->toBe('1000.00');
});

test('work day update calculates totals correctly', function () {
    // Create test data
    $today = Carbon::today();

    // Create a receipt note for purchase
    $receiptNote = ReceiptNote::factory()->create(['total' => 500, 'note_type' => ReceiptNoteType::PURCHASES]);

    // Create accountant issue note for purchase
    AccountantIssueNote::factory()->create([
        'paid' => 500,
        'for_model_type' => ReceiptNote::class,
        'for_model_id' => $receiptNote->id,
        'created_at' => $today,
    ]);

    // Create driver and accountant receipt note for sales
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 1000]);
    AccountantReceiptNote::factory()->create([
        'paid' => 800,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    // Create issue note and accountant receipt note for returns
    $issueNote = IssueNote::factory()->create(['total' => 200, 'note_type' => IssueNoteType::RETURN_PURCHASES]);
    AccountantReceiptNote::factory()->create([
        'paid' => 200,
        'from_model_type' => IssueNote::class,
        'from_model_id' => $issueNote->id,
        'created_at' => $today,
    ]);

    // Create approved expense
    Expense::factory()->create([
        'value' => 300,
        'approved_by' => User::factory()->create(),
        'created_at' => $today,
    ]);

    // Create work day with start_day
    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    // Verify calculations
    // total_day = start_day + sales - purchases - expenses + returns
    // 1000 + 800 - 500 - 300 + 200 = 1200
    expect($workDay)->toBeInstanceOf(WorkDay::class)
        ->and($workDay->total_purchase)->toBe('500.00')
        ->and($workDay->total_sales)->toBe('800.00')
        ->and($workDay->total_expenses)->toBe('300.00')
        ->and($workDay->total_purchase_returnes)->toBe('200.00')
        ->and($workDay->total_day)->toBe('1200.00');
});

test('work day update only includes today transactions', function () {
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();

    // Create yesterday's transactions
    $receiptNote = ReceiptNote::factory()->create(['note_type' => ReceiptNoteType::PURCHASES]);
    AccountantIssueNote::factory()->create([
        'paid' => 500,
        'for_model_type' => ReceiptNote::class,
        'for_model_id' => $receiptNote->id,
        'created_at' => $yesterday,
    ]);

    // Create today's transactions
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 1000]);
    AccountantReceiptNote::factory()->create([
        'paid' => 800,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay->total_purchase)->toBe('0.00')
        ->and($workDay->total_sales)->toBe('800.00')
        ->and($workDay->total_day)->toBe('1800.00'); // 1000 + 800
});

test('get today work day returns zero start day when no previous days exist', function () {
    $service = new WorkDayService();
    $workDay = $service->getToday();

    expect($workDay->start_day)->toBe('0.00');
});

test('get today work day uses most recent previous day total when multiple exist', function () {
    // Create multiple previous days
    WorkDay::create([
        'day' => Carbon::parse('2 days ago'),
        'total_day' => 500,
    ]);

    WorkDay::create([
        'day' => Carbon::yesterday(),
        'total_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->getToday();

    expect($workDay->start_day)->toBe('1000.00');
});

test('work day handles multiple transactions of same type in one day', function () {
    $today = Carbon::today();

    // Create multiple sales transactions
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 2000]);

    // First sale
    AccountantReceiptNote::factory()->create([
        'paid' => 500,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    // Second sale
    AccountantReceiptNote::factory()->create([
        'paid' => 300,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay->total_sales)->toBe('800.00')
        ->and($workDay->total_day)->toBe('1800.00'); // 1000 + 800
});

test('work day handles all transaction types in same day', function () {
    $today = Carbon::today();

    // Create a purchase
    $receiptNote = ReceiptNote::factory()->create(['total' => 300]);
    AccountantIssueNote::factory()->create([
        'paid' => 300,
        'for_model_type' => ReceiptNote::class,
        'for_model_id' => $receiptNote->id,
        'created_at' => $today,
    ]);

    // Create a sale
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 1000]);
    AccountantReceiptNote::factory()->create([
        'paid' => 500,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    // Create a purchase return
    $issueNote = IssueNote::factory()->create(['total' => 100]);
    AccountantReceiptNote::factory()->create([
        'paid' => 100,
        'from_model_type' => IssueNote::class,
        'from_model_id' => $issueNote->id,
        'created_at' => $today,
    ]);

    // Create multiple expenses
    Expense::factory()->create([
        'value' => 150,
        'approved_by' => User::factory()->create(),
        'created_at' => $today,
    ]);

    Expense::factory()->create([
        'value' => 50,
        'approved_by' => User::factory()->create(),
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    // 1000 (start) + 500 (sales) - 300 (purchases) - 200 (expenses) + 100 (returns) = 1100
    expect($workDay)
        ->total_purchase->toBe('300.00')
        ->total_sales->toBe('500.00')
        ->total_expenses->toBe('200.00')
        ->total_purchase_returnes->toBe('100.00')
        ->total_day->toBe('1100.00');
});

test('work day excludes unapproved expenses', function () {
    $today = Carbon::today();

    // Create approved expense
    Expense::factory()->create([
        'value' => 100,
        'approved_by' => User::factory()->create(),
        'created_at' => $today,
    ]);

    // Create unapproved expense
    Expense::factory()->create([
        'value' => 200,
        'approved_by' => null,
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay->total_expenses)->toBe('100.00')
        ->and($workDay->total_day)->toBe('900.00'); // 1000 - 100
});

test('work day calculations handle zero values correctly', function () {
    $today = Carbon::today();

    // Create transaction with zero value
    $driver = Driver::factory()->create();
    AccountantReceiptNote::factory()->create([
        'paid' => 0,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 0,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay)
        ->total_sales->toBe('0.00')
        ->total_purchase->toBe('0.00')
        ->total_expenses->toBe('0.00')
        ->total_purchase_returnes->toBe('0.00')
        ->total_day->toBe('0.00');
});

test('work day handles future dated transactions correctly', function () {
    $today = Carbon::today();
    $tomorrow = Carbon::tomorrow();

    // Create future transaction that should be excluded
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 2000]); // Ensure sufficient balance
    AccountantReceiptNote::factory()->create([
        'paid' => 1000,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $tomorrow,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 500,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay->total_sales)->toBe('0.00')
        ->and($workDay->total_day)->toBe('500.00'); // Only start_day, future transaction excluded
});

test('work day remains unchanged when no transactions exist', function () {
    $today = Carbon::today();

    $workDay = WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
        'total_purchase' => 0,
        'total_sales' => 0,
        'total_expenses' => 0,
        'total_purchase_returnes' => 0,
        'total_day' => 1000,
    ]);

    $service = new WorkDayService();
    $updatedWorkDay = $service->update();

    expect($updatedWorkDay->total_day)->toBe('1000.00')
        ->and($updatedWorkDay->fresh()->total_day)->toBe('1000.00');
});

test('work day can be updated multiple times in same day', function () {
    $today = Carbon::today();

    $workDay = WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();

    // First update
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 1000]); // Ensure sufficient balance
    AccountantReceiptNote::factory()->create([
        'paid' => 300,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    $workDay = $service->update();
    expect($workDay->total_sales)->toBe('300.00')
        ->and($workDay->total_day)->toBe('1300.00');

    // Second update with new transaction
    AccountantReceiptNote::factory()->create([
        'paid' => 200,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    $workDay = $service->update();
    expect($workDay->total_sales)->toBe('500.00')
        ->and($workDay->total_day)->toBe('1500.00');
});

test('work day is thread safe when updated concurrently', function () {
    $today = Carbon::today();

    $workDay = WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 1000]); // Ensure sufficient balance

    // Simulate concurrent updates by creating two service instances
    $service1 = new WorkDayService();
    $service2 = new WorkDayService();

    // Create first transaction
    AccountantReceiptNote::factory()->create([
        'paid' => 300,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    // Update from first service
    $workDay1 = $service1->update();

    // Create second transaction
    AccountantReceiptNote::factory()->create([
        'paid' => 200,
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    // Update from second service
    $workDay2 = $service2->update();

    // Final state should include both transactions
    expect($workDay2->fresh())
        ->total_sales->toBe('500.00')
        ->total_day->toBe('1500.00');
});

test('work day handles negative values correctly', function () {
    $today = Carbon::today();

    // Create a negative value expense
    Expense::factory()->create([
        'value' => -100, // Negative expense (like a refund)
        'approved_by' => User::factory()->create(),
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 1000,
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    // Negative expense should increase total_day
    expect($workDay)
        ->total_expenses->toBe('-100.00')
        ->total_day->toBe('1100.00'); // 1000 + (-(-100))
});

test('work day handles rounding of decimal values correctly', function () {
    $today = Carbon::today();

    // Create transaction with decimal values
    $driver = Driver::factory()->create();
    $driver->account()->update(['balance' => 500]); // Ensure sufficient balance
    AccountantReceiptNote::factory()->create([
        'paid' => 100.556, // Should round to 100.56
        'from_model_type' => Driver::class,
        'from_model_id' => $driver->id,
        'created_at' => $today,
    ]);

    WorkDay::create([
        'day' => $today,
        'start_day' => 100.554, // Should round to 100.55
    ]);

    $service = new WorkDayService();
    $workDay = $service->update();

    expect($workDay)
        ->start_day->toBe('100.55')
        ->total_sales->toBe('100.56')
        ->total_day->toBe('201.11'); // 100.55 + 100.56
});
