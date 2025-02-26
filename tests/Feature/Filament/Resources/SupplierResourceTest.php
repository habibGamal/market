<?php

use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use App\Filament\Resources\SupplierResource\Pages\EditSupplier;
use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Models\Supplier;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('suppliers')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
});

it('can render the index page', function () {
    livewire(ListSuppliers::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateSupplier::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Supplier::factory()->create();

    livewire(EditSupplier::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListSuppliers::class)
        ->assertTableColumnExists($column);
})->with(['name', 'phone', 'company_name', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListSuppliers::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'phone', 'company_name', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = Supplier::factory(5)->create();
    livewire(ListSuppliers::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['name', 'phone', 'company_name', 'created_at', 'updated_at']);

it('can search column', function (string $column) {
    $records = Supplier::factory(5)->create();
    $value = $records->first()->{$column};

    livewire(ListSuppliers::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name', 'phone', 'company_name']);

it('can create a record', function () {
    $record = Supplier::factory()->make();

    livewire(CreateSupplier::class)
        ->fillForm([
            'name' => $record->name,
            'phone' => $record->phone,
            'company_name' => $record->company_name,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Supplier::class, [
        'name' => $record->name,
        'phone' => $record->phone,
        'company_name' => $record->company_name,
    ]);
});

it('can update a record', function () {
    $record = Supplier::factory()->create();
    $newRecord = Supplier::factory()->make();

    livewire(EditSupplier::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => $newRecord->name,
            'phone' => $newRecord->phone,
            'company_name' => $newRecord->company_name,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Supplier::class, [
        'id' => $record->id,
        'name' => $newRecord->name,
        'phone' => $newRecord->phone,
        'company_name' => $newRecord->company_name,
    ]);
});

it('can delete a record', function () {
    $record = Supplier::factory()->create();

    livewire(EditSupplier::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Supplier::factory(5)->create();

    livewire(ListSuppliers::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can validate required', function (string $column) {
    livewire(CreateSupplier::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name', 'phone', 'company_name']);

it('has import , export action', function () {
    livewire(ListSuppliers::class)
        ->assertTableHeaderActionsExistInOrder(['export', 'import']);
});
