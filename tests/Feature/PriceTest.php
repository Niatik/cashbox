<?php

use App\Filament\Resources\PriceResource;
use App\Filament\Resources\PriceResource\Pages\EditPrice;
use App\Filament\Resources\PriceResource\RelationManagers\PriceItemsRelationManager;
use App\Models\Price;
use App\Models\PriceItem;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(PriceResource::getUrl('index'))->assertSuccessful();
});

it('can list prices', function () {
    $prices = Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->assertCanSeeTableRecords($prices);
});

it('can render page for creating the Price', function () {
    $this->get(PriceResource::getUrl('create'))->assertSuccessful();
});

it('can create a Price', function () {
    $newData = Price::factory()->make();

    livewire(PriceResource\Pages\CreatePrice::class)
        ->fillForm([
            'name' => $newData->name,
            'description' => $newData->description,
            'start_date' => $newData->start_date,
            'end_date' => $newData->end_date,
            'price' => $newData->price,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Price::class, [
        'name' => $newData->name,
        'description' => $newData->description,
        'price' => $newData->price * 100,
    ]);
});

it('can validate input to create the Price', function () {
    livewire(PriceResource\Pages\CreatePrice::class)
        ->fillForm([
            'name' => null,
            'price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'price' => 'required']);
});

it('can render page for editing the Price ', function () {
    $this->get(PriceResource::getUrl('edit', [
        'record' => Price::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Price', function () {
    $price = Price::factory()->create();

    livewire(PriceResource\Pages\EditPrice::class, [
        'record' => $price->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $price->name,
            'description' => $price->description,
            'price' => $price->price,
        ]);
});

it('can save edited Price', function () {
    $price = Price::factory()->create();
    $newData = Price::factory()->make();

    livewire(PriceResource\Pages\EditPrice::class, [
        'record' => $price->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'description' => $newData->description,
            'price' => $newData->price,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($price->refresh())
        ->name->toBe($newData->name)
        ->description->toBe($newData->description)
        ->price->toBe($newData->price);
});

it('can validate input to edit the Price', function () {
    $price = Price::factory()->create();

    livewire(PriceResource\Pages\EditPrice::class, [
        'record' => $price->getRouteKey(),
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

it('can delete the Price', function () {
    $price = Price::factory()->create();

    livewire(PriceResource\Pages\EditPrice::class, [
        'record' => $price->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($price);
});

it('can render price columns', function () {
    Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('description')
        ->assertCanRenderTableColumn('price');
});

it('can search prices by name', function () {
    $services = Price::factory()->count(10)->create();

    $name = $services->first()->name;

    livewire(PriceResource\Pages\ListPrices::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($services->where('name', $name))
        ->assertCanNotSeeTableRecords($services->where('name', '!=', $name));
});

it('can search prices by description', function () {
    $prices = Price::factory()->count(10)->create();

    $description = $prices->first()->description;

    livewire(PriceResource\Pages\ListPrices::class)
        ->searchTable($description)
        ->assertCanSeeTableRecords($prices->where('description', $description))
        ->assertCanNotSeeTableRecords($prices->where('description', '!=', $description));
});

it('can sort prices by name', function () {
    $prices = Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($prices->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($prices->sortByDesc('name'), inOrder: true);
});

it('can sort prices by description', function () {
    $prices = Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->sortTable('description')
        ->assertCanSeeTableRecords($prices->sortBy('description'), inOrder: true)
        ->sortTable('description', 'desc')
        ->assertCanSeeTableRecords($prices->sortByDesc('description'), inOrder: true);
});

it('can sort services by price', function () {
    $prices = Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->sortTable('price')
        ->assertCanSeeTableRecords($prices->sortBy('price'), inOrder: true)
        ->sortTable('price', 'desc')
        ->assertCanSeeTableRecords($prices->sortByDesc('price'), inOrder: true);
});

it('can bulk delete prices from table', function () {
    $prices = Price::factory()->count(10)->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->callTableBulkAction(DeleteBulkAction::class, $prices);

    foreach ($prices as $price) {
        $this->assertModelMissing($price);
    }
});

it('can delete prices from table', function () {
    $price = Price::factory()->create();

    livewire(PriceResource\Pages\ListPrices::class)
        ->callTableAction(TableDeleteAction::class, $price);

    $this->assertModelMissing($price);
});

it('can edit prices from table', function () {
    $price = Price::factory()->create();
    $newData = Price::factory()->make();

    livewire(PriceResource\Pages\ListPrices::class)
        ->callTableAction(EditAction::class, $price, data: [
            'name' => $newData->name,
            'description' => $newData->description,
            'price' => $newData->price,
        ])
        ->assertHasNoTableActionErrors();

    expect($price->refresh())
        ->name->toBe($newData->name)
        ->description->toBe($newData->description)
        ->price->toBe($newData->price);
});

it('can render relation manager for Price Items', function () {
    $price = Price::factory()
        ->has(PriceItem::factory()->count(3))
        ->create();

    livewire(PriceItemsRelationManager::class, [
        'ownerRecord' => $price,
        'pageClass' => EditPrice::class,
    ])
        ->assertSuccessful();
});

