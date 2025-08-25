<?php

use App\Filament\Resources\AccountantIssueNoteResource\Pages\CreateAccountantIssueNote;
use App\Filament\Resources\AccountantIssueNoteResource\Pages\ListAccountantIssueNotes;
use App\Filament\Resources\AccountantIssueNoteResource\Pages\ViewAccountantIssueNote;
use App\Models\AccountantIssueNote;
use App\Models\User;
use App\Models\ReceiptNote;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Enums\ReceiptNoteType;
use App\Enums\InvoiceStatus;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin']);
    $permissions = [
        'view_any_accountant::issue::note',
        'view_accountant::issue::note',
        'create_accountant::issue::note',
        'delete_accountant::issue::note',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
        $role->givePermissionTo($permission);
    }

    $user->assignRole($role);
    $this->actingAs($user);
});

it('can render the list page', function () {
    livewire(ListAccountantIssueNotes::class)->assertSuccessful();
});

it('can render the create page', function () {
    livewire(CreateAccountantIssueNote::class)->assertSuccessful();
});

it('can create a new accountant issue note', function () {
    $supplier = Supplier::factory()->create();
    $officer = User::factory()->create();
    $purchaseInvoice = PurchaseInvoice::factory()->create([
        'supplier_id' => $supplier->id,
        'officer_id' => $officer->id,
        'execution_date' => now(),
        'payment_date' => now(),
    ]);

    $receiptNote = ReceiptNote::factory()->create([
        'note_type' => ReceiptNoteType::PURCHASES,
        'status' => InvoiceStatus::CLOSED,
    ]);
    $purchaseInvoice->receipt_note_id = $receiptNote->id;
    $purchaseInvoice->save();

    livewire(CreateAccountantIssueNote::class)
        ->set('data.for_model_type', ReceiptNote::class)
        ->set('data.for_model_id', $receiptNote->id)
        ->assertSet('data.paid', $receiptNote->total)
        ->set('data.notes', 'Test notes')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('accountant_issue_notes', [
        'for_model_type' => ReceiptNote::class,
        'for_model_id' => $receiptNote->id,
        'notes' => 'Test notes',
    ]);
});

it('can render the view page', function () {
    $accountantIssueNote = AccountantIssueNote::factory()->create();
    livewire(ViewAccountantIssueNote::class, ['record' => $accountantIssueNote->getRouteKey()])
        ->assertSuccessful();
});
