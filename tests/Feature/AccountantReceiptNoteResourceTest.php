<?php

use App\Filament\Resources\AccountantReceiptNoteResource\Pages\CreateAccountantReceiptNote;
use App\Filament\Resources\AccountantReceiptNoteResource\Pages\EditAccountantReceiptNote;
use App\Filament\Resources\AccountantReceiptNoteResource\Pages\ListAccountantReceiptNotes;
use App\Filament\Resources\AccountantReceiptNoteResource\Pages\ViewAccountantReceiptNote;
use App\Models\AccountantReceiptNote;
use App\Models\User;
use App\Models\Driver;
use App\Models\IssueNote;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin']);
    $permissions = [
        'view_any_accountant::receipt::note',
        'view_accountant::receipt::note',
        'create_accountant::receipt::note',
        'update_accountant::receipt::note',
        'delete_accountant::receipt::note',
        'delete_any_accountant::receipt::note',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
        $role->givePermissionTo($permission);
    }

    $this->user->assignRole($role);
    $this->actingAs($this->user);

    Event::fake([
        'eloquent.created: ' . Driver::class,
    ]);

    $this->driver = Driver::factory()->create();
    $this->driver->account()->create(['balance' => 1000]);
});

it('can render the list page', function () {
    livewire(ListAccountantReceiptNotes::class)->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateAccountantReceiptNote::class)->assertSuccessful();
});

it('can create a new accountant receipt note from a driver', function () {
    livewire(CreateAccountantReceiptNote::class)
        ->set('data.from_model_type', Driver::class)
        ->set('data.from_model_id', $this->driver->id)
        ->assertSet('data.paid', $this->driver->account->balance)
        ->set('data.notes', 'Test notes from driver')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('accountant_receipt_notes', [
        'from_model_type' => Driver::class,
        'from_model_id' => $this->driver->id,
        'notes' => 'Test notes from driver',
    ]);
});

it('can create a new accountant receipt note from an issue note', function () {
    $issueNote = IssueNote::factory()->create(['total' => 500]);

    livewire(CreateAccountantReceiptNote::class)
        ->set('data.from_model_type', IssueNote::class)
        ->set('data.from_model_id', $issueNote->id)
        ->assertSet('data.paid', 500)
        ->set('data.notes', 'Test notes from issue note')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('accountant_receipt_notes', [
        'from_model_type' => IssueNote::class,
        'from_model_id' => $issueNote->id,
        'notes' => 'Test notes from issue note',
    ]);
});

it('can render the view page', function () {
    $issueNote = IssueNote::factory()->create(['total' => 500]);
    $accountantReceiptNote = AccountantReceiptNote::factory()->forIssueNote($issueNote)->byOfficer($this->user)->create(['paid' => 500]);
    livewire(ViewAccountantReceiptNote::class, ['record' => $accountantReceiptNote->getRouteKey()])
        ->assertSuccessful();
});

it('can render the edit page', function () {
    $issueNote = IssueNote::factory()->create(['total' => 500]);
    $accountantReceiptNote = AccountantReceiptNote::factory()->forIssueNote($issueNote)->byOfficer($this->user)->create(['paid' => 500]);
    livewire(EditAccountantReceiptNote::class, ['record' => $accountantReceiptNote->getRouteKey()])
        ->assertSuccessful();
});

it('can update an accountant receipt note', function () {
    $issueNote = IssueNote::factory()->create(['total' => 500]);
    $accountantReceiptNote = AccountantReceiptNote::factory()->forIssueNote($issueNote)->byOfficer($this->user)->create(['paid' => 500]);
    $newNotes = 'Updated notes';

    livewire(EditAccountantReceiptNote::class, ['record' => $accountantReceiptNote->getRouteKey()])
        ->set('data.notes', $newNotes)
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('accountant_receipt_notes', [
        'id' => $accountantReceiptNote->id,
        'notes' => $newNotes,
    ]);
});
