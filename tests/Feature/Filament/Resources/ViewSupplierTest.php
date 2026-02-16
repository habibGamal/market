<?php

use App\Filament\Resources\SupplierResource\Pages\ViewSupplier;
use App\Filament\Resources\SupplierResource\RelationManagers\PurchaseInvoicesRelationManager;
use App\Filament\Resources\SupplierResource\RelationManagers\ReturnPurchaseInvoicesRelationManager;
use App\Filament\Widgets\SupplierBalanceStats;
use App\Models\PurchaseInvoice;
use App\Models\ReturnPurchaseInvoice;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('suppliers')->truncate();
    DB::table('purchase_invoices')->truncate();
    DB::table('return_purchase_invoices')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
});

it('can render the view page', function () {
    $record = Supplier::factory()->create();

    livewire(ViewSupplier::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('can load the purchase invoices relation manager', function () {
    $record = Supplier::factory()->create();

    livewire(PurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertSuccessful();
});

it('can load the return purchase invoices relation manager', function () {
    $record = Supplier::factory()->create();

    livewire(ReturnPurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertSuccessful();
});

it('purchase invoices relation manager has correct columns', function (string $column) {
    $supplier = Supplier::factory()->create();

    livewire(PurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertTableColumnExists($column);
})->with(['id', 'total', 'status', 'officer.name', 'execution_date', 'payment_date', 'created_at']);

it('return purchase invoices relation manager has correct columns', function (string $column) {
    $supplier = Supplier::factory()->create();

    livewire(ReturnPurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertTableColumnExists($column);
})->with(['id', 'total', 'status', 'officer.name', 'created_at']);

it('shows purchase invoices for the supplier', function () {
    $supplier = Supplier::factory()->create();
    $invoices = PurchaseInvoice::factory(3)->create(['supplier_id' => $supplier->id]);
    $otherInvoices = PurchaseInvoice::factory(2)->create();

    livewire(PurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertCanSeeTableRecords($invoices)
        ->assertCanNotSeeTableRecords($otherInvoices);
});

it('shows return purchase invoices for the supplier', function () {
    $supplier = Supplier::factory()->create();
    $invoices = ReturnPurchaseInvoice::factory(3)->create(['supplier_id' => $supplier->id]);
    $otherInvoices = ReturnPurchaseInvoice::factory(2)->create();

    livewire(ReturnPurchaseInvoicesRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ViewSupplier::class,
    ])
        ->assertCanSeeTableRecords($invoices)
        ->assertCanNotSeeTableRecords($otherInvoices);
});

it('has filter action on view page', function () {
    $record = Supplier::factory()->create();

    livewire(ViewSupplier::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('filter');
});

it('has edit action on view page', function () {
    $record = Supplier::factory()->create();

    livewire(ViewSupplier::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('edit');
});
