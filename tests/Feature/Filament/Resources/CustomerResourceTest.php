<?php

use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use App\Models\Area;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;

use function Pest\Livewire\livewire;

uses(WithFaker::class);

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('customers')->truncate();
    DB::table('permissions')->truncate();
    DB::table('areas')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    Storage::fake('public');

    // Create required permissions
    $permissions = [
        'view_any_customer',
        'view_customer',
        'create_customer',
        'update_customer',
        'delete_customer'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create and authenticate a user with necessary permissions
    $user = User::factory()->create();
    $user->givePermissionTo($permissions);
    auth()->login($user);
});

it('can render the index page', function () {
    livewire(ListCustomers::class)
        ->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateCustomer::class)
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $record = Customer::factory()->create();

    livewire(EditCustomer::class, ['record' => $record->getRouteKey()])
        ->assertSuccessful();
});

it('has required columns', function (string $column) {
    livewire(ListCustomers::class)
        ->assertTableColumnExists($column);
})->with([
    'name',
    'phone',
    'area.name',
    'gov',
    'city',
    'rating_points',
    'postpaid_balance',
    'blocked',
    'created_at'
]);

it('can render all columns', function (string $column) {
    livewire(ListCustomers::class)
        ->assertCanRenderTableColumn($column);
})->with([
    'name',
    'phone',
    'area.name',
    'gov',
    'city',
    'rating_points',
    'postpaid_balance',
    'blocked',
    'created_at'
]);

it('can sort standard columns', function (string $column) {
    Customer::factory(5)->create();

    livewire(ListCustomers::class)
        ->sortTable($column)
        ->assertCanSeeTableRecords(Customer::query()->orderBy($column)->get())
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords(Customer::query()->orderBy($column, 'desc')->get());
})->with([
    'name',
    'rating_points',
    'postpaid_balance',
    'created_at'
]);

it('can filter records by area', function () {
    $area = Area::factory()->create(['name' => 'Area Test']);
    $customers = Customer::factory()->count(3)->create(['area_id' => $area->id]);
    $otherArea = Area::factory()->create(['name' => 'Other Area']);
    $otherCustomers = Customer::factory()->count(2)->create(['area_id' => $otherArea->id]);

    livewire(ListCustomers::class)
        ->filterTable('area', $area->id)
        ->assertCanSeeTableRecords($customers)
        ->assertCanNotSeeTableRecords($otherCustomers);
});

it('can filter records by blocked status', function () {
    $blockedCustomers = Customer::factory()->count(2)->create(['blocked' => true]);
    $activeCustomers = Customer::factory()->count(3)->create(['blocked' => false]);

    livewire(ListCustomers::class)
        ->filterTable('blocked', true)
        ->assertCanSeeTableRecords($blockedCustomers)
        ->assertCanNotSeeTableRecords($activeCustomers);
});

it('can validate create form', function () {
    $area = Area::factory()->create();

    livewire(CreateCustomer::class)
        ->fillForm([
            'name' => '',
            'phone' => '',
            'area_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'phone' => 'required',
            'area_id' => 'required',
        ]);
});

it('can create a customer', function () {
    $area = Area::factory()->create();
    $newData = [
        'name' => 'Test Customer',
        'location' => 'Test Location',
        'gov' => 'Test Gov',
        'city' => 'Test City',
        'village' => 'Test Village',
        'area_id' => $area->id,
        'address' => 'Test Address',
        'phone' => '123456789',
        'email' => 'test@example.com',
        'password' => 'password',
        'rating_points' => 0,
        'postpaid_balance' => 0,
        'blocked' => false,
    ];

    livewire(CreateCustomer::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('customers', [
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);
});
