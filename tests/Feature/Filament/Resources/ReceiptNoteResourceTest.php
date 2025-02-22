<?php

use App\Enums\InvoiceStatus;
use App\Enums\ReceiptNoteType;
use App\Filament\Resources\ReceiptNoteResource\Pages\CreateReceiptNote;
use App\Filament\Resources\ReceiptNoteResource\Pages\EditReceiptNote;
use App\Filament\Resources\ReceiptNoteResource\Pages\ListReceiptNotes;
use App\Filament\Resources\ReceiptNoteResource\Pages\ViewReceiptNote;
use App\Models\ReceiptNote;
use App\Models\Product;
use App\Models\User;
use App\Models\PurchaseInvoice;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('receipt_note_items')->truncate();
    DB::table('receipt_notes')->truncate();
    DB::table('permissions')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    Storage::fake('public');

    // Create required permissions
    $permissions = [
        'view_any_receipt::note',
        'view_receipt::note',
        'create_receipt::note',
        'update_receipt::note',
        'delete_receipt::note',
        'show_costs_receipt::note'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create and authenticate a user with necessary permissions
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);
    auth()->login($user);

    test()->user = $user; // Make the user available to test methods
});

it('can render the index page', function () {
    livewire(ListReceiptNotes::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateReceiptNote::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = ReceiptNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'total' => 0,
        'note_type' => ReceiptNoteType::PURCHASES,
        'officer_id' => $this->user->id,
    ]);

    livewire(EditReceiptNote::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('can render the view page', function () {
    $record = ReceiptNote::factory()->create([
        'total' => 0,
        'note_type' => ReceiptNoteType::PURCHASES,
        'officer_id' => $this->user->id,
    ]);

    livewire(ViewReceiptNote::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    $record = ReceiptNote::factory()->create([
        'total' => 0,
        'note_type' => ReceiptNoteType::PURCHASES,
        'officer_id' => $this->user->id,
    ]);

    livewire(ListReceiptNotes::class)
        ->assertSuccessful()
        ->assertTableColumnExists($column);
})->with([
            'id',
            'total',
            'status',
            'officer.name',
            'created_at',
            'updated_at'
        ]);

it('can sort column', function (string $column) {
    $records = ReceiptNote::factory(5)->create([
        'total' => 0,
        'note_type' => ReceiptNoteType::PURCHASES,
        'officer_id' => $this->user->id,
    ]);

    livewire(ListReceiptNotes::class)
        ->assertSuccessful()
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with([
            'id',
            'total',
            'created_at',
            'updated_at'
        ]);

it('can search column', function (string $column) {
    $records = ReceiptNote::factory(5)->create([
        'total' => 0,
        'note_type' => ReceiptNoteType::PURCHASES,
        'officer_id' => $this->user->id,
    ]);

    $value = $records->first()->{$column};

    livewire(ListReceiptNotes::class)
        ->assertSuccessful()
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['id']);


it('validates release dates total quantity matches expected quantity', function () {
    $record = ReceiptNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'note_type' => ReceiptNoteType::PURCHASES,
        'total' => 500,
        'officer_id' => $this->user->id,
    ]);

    $product = Product::factory()->create([
        'packet_to_piece' => 10, // Each packet contains 10 pieces
    ]);

    $items = [
        [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'packets_quantity' => 5, // Should result in 50 pieces total
            'piece_quantity' => 0,
            'packet_cost' => 100,
            'release_dates' => [
                [
                    'piece_quantity' => 20, // First batch
                    'release_date' => now()->format('Y-m-d'),
                ],
                [
                    'piece_quantity' => 20, // Second batch - Total 40 pieces (incorrect, should be 50)
                    'release_date' => now()->addDay()->format('Y-m-d'),
                ]
            ],
            'reference_state' => [
                'product' => $product->toArray(),
            ],
            'total' => 500,
        ]
    ];

    livewire(EditReceiptNote::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful()
        ->fillForm([
            'items' => $items,
        ])
        ->call('save')
        ->assertHasFormErrors(); // Should fail because total pieces (40) doesn't match expected (50)
})->skip();

it('can delete a record', function () {
    $record = ReceiptNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'note_type' => ReceiptNoteType::PURCHASES,
        'total' => 0,
        'officer_id' => test()->user->id,
    ]);

    livewire(EditReceiptNote::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('cannot delete a record if status is closed', function () {
    $record = ReceiptNote::factory()->create([
        'status' => InvoiceStatus::CLOSED,
        'note_type' => ReceiptNoteType::PURCHASES,
        'total' => 0,
        'officer_id' => test()->user->id,
    ]);

    livewire(ListReceiptNotes::class)
        ->assertActionDoesNotExist('delete');

    $this->assertModelExists($record);
});

it('can create from purchase invoice', function () {
    $purchaseInvoice = PurchaseInvoice::factory()->create([
        'status' => InvoiceStatus::CLOSED,
        'total' => 1000,
        'officer_id' => test()->user->id,
    ]);

    $product = Product::factory()->create([
        'packet_to_piece' => 10
    ]);

    $purchaseInvoice->items()->create([
        'product_id' => $product->id,
        'packets_quantity' => 5,
        'packet_cost' => 100,
        'total' => 500,
    ]);

    livewire(CreateReceiptNote::class)
        ->assertActionExists('from_purchase_invoice')
        ->callAction('from_purchase_invoice', [
            'purchase_invoice_id' => $purchaseInvoice->id,
        ]);

    $receipt = ReceiptNote::latest()->first();

    $this->assertDatabaseHas('receipt_notes', [
        'id' => $receipt->id,
        'note_type' => ReceiptNoteType::PURCHASES,
        'status' => InvoiceStatus::DRAFT,
        'total' => $purchaseInvoice->total,
        'officer_id' => test()->user->id,
    ]);

    $this->assertDatabaseHas('receipt_note_items', [
        'receipt_note_id' => $receipt->id,
        'product_id' => $product->id,
        'packets_quantity' => 5,
        'packet_cost' => 100,
    ]);
});

it('validates item quantities', function () {
    $record = ReceiptNote::factory()->create([
        'status' => InvoiceStatus::DRAFT,
        'note_type' => ReceiptNoteType::PURCHASES,
        'total' => 500,
        'officer_id' => test()->user->id,
    ]);

    $product = Product::factory()->create([
        'packet_to_piece' => 10
    ]);

    $items = [
        [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'packets_quantity' => -1, // Invalid negative quantity
            'piece_quantity' => 0,
            'packet_cost' => 100,
            'release_dates' => [
                [
                    'piece_quantity' => 50,
                    'release_date' => now()->format('Y-m-d'),
                ]
            ],
            'reference_state' => [
                'product' => $product->toArray(),
            ],
            'total' => 500,
        ]
    ];

    livewire(EditReceiptNote::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'items' => $items,
        ])
        ->call('save')
        ->assertHasFormErrors(['items.0.packets_quantity']);
});
