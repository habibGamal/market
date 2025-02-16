<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\RelationManagers\LimitsRelationManager;
use App\Models\Product;
use App\Models\ProductLimit;
use App\Models\Area;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    DB::table('product_limits')->truncate();
    DB::table('products')->truncate();

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListProducts::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateProduct::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has column', function (string $column) {
    livewire(ListProducts::class)
        ->assertTableColumnExists($column);
})->with(['name', 'barcode', 'packet_price', 'piece_price', 'expiration', 'brand.name', 'category.name', 'created_at', 'updated_at']);

it('can render column', function (string $column) {
    livewire(ListProducts::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'barcode', 'packet_price', 'piece_price', 'expiration', 'brand.name', 'category.name', 'created_at', 'updated_at']);


it('can sort column', function (string $column) {
    $records = Product::factory(5)->create();
    livewire(ListProducts::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with([
            'name',
            'barcode',
            'packet_price',
            'piece_price',
            'brand.name',
            'category.name',
            'created_at',
            'updated_at'
        ]);

it('can search column', function (string $column) {
    $records = Product::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListProducts::class)
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name', 'barcode']);

it('can create a record', function () {
    $record = Product::factory()->make();

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => $record->name,
            'barcode' => $record->barcode,
            'packet_to_piece' => $record->packet_to_piece,
            'packet_cost' => $record->packet_cost,
            'packet_price' => $record->packet_price,
            'piece_price' => $record->piece_price,
            'before_discount.packet_price' => $record->before_discount['packet_price'],
            'before_discount.piece_price' => $record->before_discount['piece_price'],
            'expiration_duration' => $record->expiration_duration,
            'expiration_unit' => $record->expiration_unit,
            'brand_id' => $record->brand_id,
            'category_id' => $record->category_id,
        ])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => $record->name,
        'barcode' => $record->barcode,
    ]);
});

it('can update a record', function () {
    $record = Product::factory()->create();
    $newRecord = Product::factory()->make();

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'name' => $newRecord->name,
            'barcode' => $newRecord->barcode,
            'packet_to_piece' => $newRecord->packet_to_piece,
            'packet_cost' => $newRecord->packet_cost,
            'packet_price' => $newRecord->packet_price,
            'piece_price' => $newRecord->piece_price,
            'before_discount.packet_price' => $newRecord->before_discount['packet_price'],
            'before_discount.piece_price' => $newRecord->before_discount['piece_price'],
            'expiration_duration' => $newRecord->expiration_duration,
            'expiration_unit' => $newRecord->expiration_unit,
            'brand_id' => $newRecord->brand_id,
            'category_id' => $newRecord->category_id,
        ])
        ->assertActionExists('save')
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => $newRecord->name,
        'barcode' => $newRecord->barcode,
    ]);
});

it('can delete a record', function () {
    $record = Product::factory()->create();

    livewire(EditProduct::class, ['record' => $record->getRouteKey()])
        ->assertActionExists('delete')
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($record);
});

it('can bulk delete records', function () {
    $records = Product::factory(5)->create();

    livewire(ListProducts::class)
        ->assertTableBulkActionExists('delete')
        ->callTableBulkAction(DeleteBulkAction::class, $records);

    foreach ($records as $record) {
        $this->assertModelMissing($record);
    }
});

it('can validate required', function (string $column) {
    livewire(CreateProduct::class)
        ->fillForm([$column => null])
        ->assertActionExists('create')
        ->call('create')
        ->assertHasFormErrors([$column => ['required']]);
})->with(['name', 'barcode', 'packet_to_piece', 'packet_cost', 'packet_price', 'piece_price', 'before_discount.packet_price', 'before_discount.piece_price', 'expiration_duration', 'expiration_unit', 'brand_id', 'category_id']);

// it('can validate unique', function (string $column) {
//     $record = Product::factory()->create();

//     livewire(CreateProduct::class)
//         ->fillForm(['name' => $record->name])
//         ->assertActionExists('create')
//         ->call('create')
//         ->assertHasFormErrors([$column => ['unique']]);
// })->with(['name', 'barcode']);

it('has import , export action', function () {
    livewire(ListProducts::class)
        ->assertTableHeaderActionsExistInOrder(['export', 'import']);
});

// LimitsRelationManager Tests
it('has limits relation manager', function () {
    $product = Product::factory()->create();

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])->assertSuccessful();
});

it('can list limits', function () {
    $product = Product::factory()->create();
    $limits = ProductLimit::factory(3)->create(['product_id' => $product->id]);

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($limits);
});

it('has limits table columns', function (string $column) {
    $product = Product::factory()->create();

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->assertSuccessful()
        ->assertTableColumnExists($column);
})->with(['area.name', 'min_packets', 'max_packets', 'min_pieces', 'max_pieces']);

it('can create a limit', function () {
    $product = Product::factory()->create();
    $area = Area::factory()->create();
    $data = [
        'area_id' => $area->id,
        'min_packets' => fake()->numberBetween(1, 10),
        'max_packets' => fake()->numberBetween(11, 20),
        'min_pieces' => fake()->numberBetween(1, 10),
        'max_pieces' => fake()->numberBetween(11, 20),
    ];

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->mountTableAction('create')
        ->setTableActionData($data)
        ->callTableAction('create');

    $this->assertDatabaseHas('product_limits', [
        'product_id' => $product->id,
        'area_id' => $area->id,
        'min_packets' => $data['min_packets'],
        'max_packets' => $data['max_packets'],
        'min_pieces' => $data['min_pieces'],
        'max_pieces' => $data['max_pieces'],
    ]);
});

it('can edit a limit', function () {
    $product = Product::factory()->create();
    $limit = ProductLimit::factory()->create(['product_id' => $product->id]);
    $area = Area::factory()->create();
    $data = [
        'area_id' => $area->id,
        'min_packets' => fake()->numberBetween(1, 10),
        'max_packets' => fake()->numberBetween(11, 20),
        'min_pieces' => fake()->numberBetween(1, 10),
        'max_pieces' => fake()->numberBetween(11, 20),
    ];

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->mountTableAction('edit', record: $limit)
        ->setTableActionData($data)
        ->callTableAction('edit', record: $limit);

    $this->assertDatabaseHas('product_limits', [
        'id' => $limit->id,
        'product_id' => $product->id,
        'area_id' => $area->id,
        'min_packets' => $data['min_packets'],
        'max_packets' => $data['max_packets'],
        'min_pieces' => $data['min_pieces'],
        'max_pieces' => $data['max_pieces'],
    ]);
});

it('can delete a limit', function () {
    $product = Product::factory()->create();
    $limit = ProductLimit::factory()->create(['product_id' => $product->id]);

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->mountTableAction('delete', record: $limit)
        ->callTableAction('delete', record: $limit);

    $this->assertModelMissing($limit);
});

it('can validate required fields in limits', function (string $field) {
    $product = Product::factory()->create();

    livewire(LimitsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class
    ])
        ->mountTableAction('create')
        ->setTableActionData([$field => null])
        ->callTableAction('create')
        ->assertHasTableActionErrors([$field => ['required']]);
})->with(['area_id', 'min_packets', 'max_packets', 'min_pieces', 'max_pieces']);
