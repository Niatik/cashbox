<?php

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});

it('can render page', function () {
    $this->get(ServiceResource::getUrl('index'))->assertSuccessful();
});

it('can list services', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->assertCanSeeTableRecords($services);
});

it('can render page for creating the Service', function () {
    $this->get(ServiceResource::getUrl('create'))->assertSuccessful();
});

it('can create a Service', function () {
    $newData = Service::factory()->make();

    livewire(ServiceResource\Pages\CreateService::class)
        ->fillForm([
            'name' => $newData->name,
            'description' => $newData->description,
            'start_date' => $newData->start_date,
            'end_date' => $newData->end_date,
            'price' => $newData->price,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Service::class, [
        'name' => $newData->name,
        'description' => $newData->description,
        'price' => $newData->price,
    ]);
});

it('can validate input', function () {
    livewire(ServiceResource\Pages\CreateService::class)
        ->fillForm([
            'name' => null,
            'price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'price' => 'required']);
});

it('can render page for editing the Service ', function () {
    $this->get(ServiceResource::getUrl('edit', [
        'record' => Service::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Service', function () {
    $service = Service::factory()->create();

    livewire(ServiceResource\Pages\EditService::class, [
        'record' => $service->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $service->name,
            'description' => $service->description,
            'price' => $service->price,
        ]);
});


