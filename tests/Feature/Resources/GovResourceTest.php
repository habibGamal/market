<?php

use App\Models\Gov;
use App\Models\User;
use App\Filament\Resources\GovResource;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render gov list', function () {
    $govs = Gov::factory()->count(3)->create();

    livewire(GovResource\Pages\ListGovs::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($govs);
});

it('can render create gov form', function () {
    livewire(GovResource\Pages\CreateGov::class)
        ->assertSuccessful();
});

it('can create gov', function () {
    $newData = [
        'name' => 'القاهرة',
    ];

    livewire(GovResource\Pages\CreateGov::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Gov::class, $newData);
});

it('can validate input', function () {
    livewire(GovResource\Pages\CreateGov::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can render edit gov form', function () {
    $gov = Gov::factory()->create();

    livewire(GovResource\Pages\EditGov::class, [
        'record' => $gov->getRouteKey(),
    ])
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $gov->name,
        ]);
});

it('can edit gov', function () {
    $gov = Gov::factory()->create();
    $newData = ['name' => 'الجيزة'];

    livewire(GovResource\Pages\EditGov::class, [
        'record' => $gov->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($gov->refresh())
        ->name->toBe($newData['name']);
});

it('can delete gov', function () {
    $gov = Gov::factory()->create();

    livewire(GovResource\Pages\ListGovs::class)
        ->callTableAction('delete', $gov);

    $this->assertModelMissing($gov);
});
