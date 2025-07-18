<?php

use App\Enums\InvoiceStatus;
use App\Filament\Resources\PurchaseInvoiceResource\Pages\CreatePurchaseInvoice;
use App\Filament\Resources\PurchaseInvoiceResource\Pages\EditPurchaseInvoice;
use App\Filament\Resources\PurchaseInvoiceResource\Pages\ListPurchaseInvoices;
use App\Filament\Resources\PurchaseInvoiceResource\Pages\ViewPurchaseInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('purchase_invoice_items')->truncate();
    DB::table('purchase_invoices')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListPurchaseInvoices::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreatePurchaseInvoice::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = PurchaseInvoice::factory()->create(
        [
            'status' => InvoiceStatus::DRAFT,
        ]
    );

    livewire(EditPurchaseInvoice::class, ['record' => $record->getRouteKey()])
        ->assertStatus(200);
});

it('can render the view page', function () {
    $record = PurchaseInvoice::factory()->create();

    livewire(ViewPurchaseInvoice::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListPurchaseInvoices::class)
        ->assertTableColumnExists($column);
})->with([
            'id',
            'total',
            'status',
            'officer.name',
            'created_at',
            'updated_at'
        ]);

it('can render column', function (string $column) {
    livewire(ListPurchaseInvoices::class)
        ->assertCanRenderTableColumn($column);
})->with([
            'id',
            'total',
            'status',
            'officer.name',
            'created_at',
            'updated_at'
        ]);

it('can sort column', function (string $column) {
    $records = PurchaseInvoice::factory(5)->create();
    livewire(ListPurchaseInvoices::class)
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
    $records = PurchaseInvoice::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListPurchaseInvoices::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['id']);

it('can create a record', function () {
    $record = PurchaseInvoice::factory()->make(
        [
            'status' => InvoiceStatus::DRAFT,
        ]
    );
    $product = Product::factory()->create();

    $items = [
        [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'packets_quantity' => 5,
            'piece_quantity' => 0,
            'packet_cost' => 100,
            'total' => 500,
        ]
    ];

    $total = array_sum(array_column($items, 'total'));

    livewire(CreatePurchaseInvoice::class)
        ->set('data.items', null)
        ->fillForm([
            'status' => $record->status,
            'items' => $items,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(PurchaseInvoice::class, [
        'total' => $total,
        'status' => $record->status,
    ]);

    $invoice = PurchaseInvoice::latest()->first();
    $this->assertDatabaseHas('purchase_invoice_items', [
        'purchase_invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'packets_quantity' => 5,
        'piece_quantity' => 0,
        'packet_cost' => 100,
    ]);
});

it('can update a record', function () {
    $record = PurchaseInvoice::factory()->create([
        'status' => InvoiceStatus::DRAFT,
    ]);

    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $items = [
        [
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'packets_quantity' => 5,
            'packet_cost' => 100,
            'total' => 500,
        ],
        [
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'packets_quantity' => 3,
            'packet_cost' => 200,
            'total' => 600,
        ]
    ];

    $total = array_sum(array_column($items, 'total'));

    livewire(EditPurchaseInvoice::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'total' => $total,
            'status' => $record->status,
            'items' => $items,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(PurchaseInvoice::class, [
        'id' => $record->id,
        'total' => $total,
        'status' => $record->status,
    ]);

    foreach ($items as $item) {
        $this->assertDatabaseHas('purchase_invoice_items', [
            'purchase_invoice_id' => $record->id,
            'product_id' => $item['product_id'],
            'packets_quantity' => $item['packets_quantity'],
            'packet_cost' => $item['packet_cost'],
        ]);
    }
});

it('can delete a record', function () {
    $record = PurchaseInvoice::factory()->create(
        [
            'status' => InvoiceStatus::DRAFT,
        ]
    );

    livewire(EditPurchaseInvoice::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('cannot delete a record if status is closed', function () {
    $record = PurchaseInvoice::factory()->create([
        'status' => InvoiceStatus::CLOSED,
    ]);

    livewire(ListPurchaseInvoices::class, ['record' => $record->getRouteKey()])
        ->assertActionDoesNotExist('delete');

    $this->assertModelExists($record);
});

it('cannot edit a record if status is closed', function () {
    $record = PurchaseInvoice::factory()->create([
        'status' => InvoiceStatus::CLOSED,
    ]);

    livewire(EditPurchaseInvoice::class, ['record' => $record->getRouteKey()])
        ->assertStatus(403);
});

it('has export action', function () {
    livewire(ListPurchaseInvoices::class)
        ->assertTableActionExists('export');
});


it('correctly calculates totals', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $items = [
        [
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'packets_quantity' => 5,
            'packet_cost' => 100,
            'total' => 500,
        ],
        [
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'packets_quantity' => 3,
            'packet_cost' => 200,
            'total' => 600,
        ]
    ];

    livewire(CreatePurchaseInvoice::class)
        ->set('data.items', null)
        ->fillForm([
            'items' => $items,
            'status' => InvoiceStatus::DRAFT,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $invoice = PurchaseInvoice::latest()->first();
    $this->assertEquals(1100, $invoice->total); // 500 + 600
});
