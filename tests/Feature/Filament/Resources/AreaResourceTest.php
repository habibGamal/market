<?php

use App\Filament\Resources\AreaResource\Pages\CreateArea;
use App\Filament\Resources\AreaResource\Pages\EditArea;
use App\Filament\Resources\AreaResource\Pages\ListAreas;
use App\Models\Area;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('areas')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListAreas::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateArea::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Area::factory()->create();

    livewire(EditArea::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListAreas::class)
        ->assertTableColumnExists($column);
})->with(['name', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListAreas::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = Area::factory(5)->create();

    livewire(ListAreas::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['name', 'created_at', 'updated_at']);

it('can search column', function (string $column) {
    $records = Area::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListAreas::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name']);

it('can create a record', function () {
    $record = Area::factory()->make();

    livewire(CreateArea::class)
        ->fillForm([
            'name' => $record->name,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Area::class, [
        'name' => $record->name,
    ]);
});

it('can update a record', function () {
    $record = Area::factory()->create();
    $newRecord = Area::factory()->make();

    livewire(EditArea::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => $newRecord->name,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Area::class, [
        'name' => $newRecord->name,
    ]);
});

it('can delete a record', function () {
    $record = Area::factory()->create();

    livewire(EditArea::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Area::factory(5)->create();

    livewire(ListAreas::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can validate required', function (string $column) {
    livewire(CreateArea::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name']);

it('can validate unique', function (string $column) {
    $record = Area::factory()->create();

    livewire(CreateArea::class)
        ->fillForm(['name' => $record->name])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['unique']]);
})->with(['name']);


it('has import , export action', function () {
    livewire(ListAreas::class)
        ->assertTableHeaderActionsExistInOrder(['export', 'import']);
});
