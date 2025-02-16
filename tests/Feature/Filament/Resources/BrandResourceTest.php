<?php
use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Models\Brand;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('brands')->truncate();
    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListBrands::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateBrand::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Brand::factory()->create();

    livewire(EditBrand::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListBrands::class)
        ->assertTableColumnExists($column);
})->with(['name', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListBrands::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = Brand::factory(5)->create();

    livewire(ListBrands::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['name', 'created_at', 'updated_at']);

it('can search column', function (string $column) {
    $records = Brand::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListBrands::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name']);

it('can create a record', function () {
    Storage::fake('public');

    $record = Brand::factory()->make();
    $image = UploadedFile::fake()->image('brand.jpg');

    livewire(CreateBrand::class)
        ->fillForm([
            'name' => $record->name,
        ])
        ->set('data.image', $image)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Brand::class, [
        'name' => $record->name,
    ]);
});

it('can update a record', function () {
    Storage::fake('public');

    $record = Brand::factory()->create();
    $newRecord = Brand::factory()->make();
    $image = UploadedFile::fake()->image('brand.jpg');

    livewire(EditBrand::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => $newRecord->name,
        ])
        ->set('data.image', $image)
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Brand::class, [
        'name' => $newRecord->name,
    ]);
});

it('can delete a record', function () {
    $record = Brand::factory()->create();

    livewire(EditBrand::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Brand::factory(5)->create();

    livewire(ListBrands::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can validate required', function (string $column) {
    livewire(CreateBrand::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name', 'image']);

it('can validate unique', function (string $column) {
    $record = Brand::factory()->create();

    livewire(CreateBrand::class)
        ->fillForm(['name' => $record->name])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['unique']]);
})->with(['name']);

it('has import , export action', function () {
    livewire(ListBrands::class)
        ->assertTableHeaderActionsExistInOrder(['export', 'import']);
});
