<?php

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

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
        'price' => $newData->price * 100,
    ]);
});

it('can validate input to create the Service', function () {
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

it('can save edited Service', function () {
    $service = Service::factory()->create();
    $newData = Service::factory()->make();

    livewire(ServiceResource\Pages\EditService::class, [
        'record' => $service->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'description' => $newData->description,
            'price' => $newData->price,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->refresh())
        ->name->toBe($newData->name)
        ->description->toBe($newData->description)
        ->price->toBe($newData->price);
});

it('can validate input to edit the Service', function () {
    $service = Service::factory()->create();

    livewire(ServiceResource\Pages\EditService::class, [
        'record' => $service->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
            'price' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'price' => 'required',
        ]);
});

it('can delete the Service', function () {
    $service = Service::factory()->create();

    livewire(ServiceResource\Pages\EditService::class, [
        'record' => $service->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($service);
});

it('can render service columns', function () {
    Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('description')
        ->assertCanRenderTableColumn('price');
});

it('can search services by name', function () {
    $services = Service::factory()->count(10)->create();

    $name = $services->first()->name;

    livewire(ServiceResource\Pages\ListServices::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($services->where('name', $name))
        ->assertCanNotSeeTableRecords($services->where('name', '!=', $name));
});

it('can search services by description', function () {
    $services = Service::factory()->count(10)->create();

    $description = $services->first()->description;

    livewire(ServiceResource\Pages\ListServices::class)
        ->searchTable($description)
        ->assertCanSeeTableRecords($services->where('description', $description))
        ->assertCanNotSeeTableRecords($services->where('description', '!=', $description));
});

it('can sort services by name', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($services->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($services->sortByDesc('name'), inOrder: true);
});

it('can sort services by description', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->sortTable('description')
        ->assertCanSeeTableRecords($services->sortBy('description'), inOrder: true)
        ->sortTable('description', 'desc')
        ->assertCanSeeTableRecords($services->sortByDesc('description'), inOrder: true);
});

it('can sort services by price', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->sortTable('price')
        ->assertCanSeeTableRecords($services->sortBy('price'), inOrder: true)
        ->sortTable('price', 'desc')
        ->assertCanSeeTableRecords($services->sortByDesc('price'), inOrder: true);
});

it('can bulk delete services from table', function () {
    $services = Service::factory()->count(10)->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->callTableBulkAction(DeleteBulkAction::class, $services);

    foreach ($services as $service) {
        $this->assertModelMissing($service);
    }
});

it('can delete services from table', function () {
    $service = Service::factory()->create();

    livewire(ServiceResource\Pages\ListServices::class)
        ->callTableAction(TableDeleteAction::class, $service);

    $this->assertModelMissing($service);
});

it('can edit services from table', function () {
    $service = Service::factory()->create();
    $newData = Service::factory()->make();

    livewire(ServiceResource\Pages\ListServices::class)
        ->callTableAction(EditAction::class, $service, data: [
            'name' => $newData->name,
            'description' => $newData->description,
            'price' => $newData->price,
        ])
        ->assertHasNoTableActionErrors();

    expect($service->refresh())
        ->name->toBe($newData->name)
        ->description->toBe($newData->description)
        ->price->toBe($newData->price);
});
