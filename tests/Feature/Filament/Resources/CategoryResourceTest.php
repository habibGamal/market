<?php

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // First truncate product_limits since it depends on products
    DB::table('product_limits')->truncate();
    // Then truncate products since it depends on categories
    DB::table('products')->truncate();
    // Finally truncate categories
    DB::table('categories')->truncate();

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListCategories::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateCategory::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListCategories::class)
        ->assertTableColumnExists($column);
})->with(['name', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListCategories::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'created_at', 'updated_at']);

it('can sort column', function (string $column) {
    $records = Category::factory(5)->create();

    livewire(ListCategories::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['name', 'created_at', 'updated_at']);

it('can search column', function (string $column) {
    $records = Category::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListCategories::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name']);

it('can create a record', function () {
    $record = Category::factory()->make();
    $file = UploadedFile::fake()->image('category.jpg');

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => $record->name,
            'image' => $file,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => $record->name,
    ]);

    // Assert that at least one file exists in the categories-images directory
    $files = Storage::disk('public')->files('categories-images');
    $this->assertNotEmpty($files, 'No files found in categories-images directory');
});

it('can update a record', function () {
    $record = Category::factory()->create();
    $newRecord = Category::factory()->make();
    $file = UploadedFile::fake()->image('updated_category.jpg');

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => $newRecord->name,
            'image' => $file,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => $newRecord->name,
    ]);

    // Assert that at least one file exists in the categories-images directory
    $files = Storage::disk('public')->files('categories-images');
    $this->assertNotEmpty($files, 'No files found in categories-images directory');
});

it('can delete a record', function () {
    $record = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Category::factory(5)->create();

    livewire(ListCategories::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can validate required', function (string $column) {
    livewire(CreateCategory::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name', 'image']);

it('can validate unique', function (string $column) {
    $record = Category::factory()->create();

    livewire(CreateCategory::class)
        ->fillForm(['name' => $record->name])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['unique']]);
})->with(['name']);
