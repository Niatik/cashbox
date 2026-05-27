<?php

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(ProductResource::getUrl('index'))->assertSuccessful();
});

it('can list products', function () {
    $products = Product::factory()->count(10)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can render page for creating the Product', function () {
    $this->get(ProductResource::getUrl('create'))->assertSuccessful();
});

it('can create a Product', function () {
    $newData = Product::factory()->make();

    livewire(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name' => $newData->name,
            'price' => $newData->price,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => $newData->name,
        'price' => $newData->price * 100,
    ]);
});

it('can validate input to create the Product', function () {
    livewire(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name' => null,
            'price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'price' => 'required']);
});

it('can render page for editing the Product', function () {
    $this->get(ProductResource::getUrl('edit', [
        'record' => Product::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Product', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $product->name,
            'price' => $product->price,
        ]);
});

it('can save edited Product', function () {
    $product = Product::factory()->create();
    $newData = Product::factory()->make();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'price' => $newData->price,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->name->toBe($newData->name)
        ->price->toBe($newData->price);
});

it('can validate input to edit the Product', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
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

it('can delete the Product', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($product);
});

it('can render product columns', function () {
    Product::factory()->count(10)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('price');
});

it('can search products by name', function () {
    $products = Product::factory()->count(10)->create();

    $name = $products->first()->name;

    livewire(ProductResource\Pages\ListProducts::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($products->where('name', $name))
        ->assertCanNotSeeTableRecords($products->where('name', '!=', $name));
});

it('can sort products by name', function () {
    $products = Product::factory()->count(10)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($products->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('name'), inOrder: true);
});

it('can sort products by price', function () {
    $products = Product::factory()->count(10)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->sortTable('price')
        ->assertCanSeeTableRecords($products->sortBy('price'), inOrder: true)
        ->sortTable('price', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('price'), inOrder: true);
});

it('can bulk delete products from table', function () {
    $products = Product::factory()->count(10)->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $products);

    foreach ($products as $product) {
        $this->assertModelMissing($product);
    }
});

it('can delete products from table', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\Pages\ListProducts::class)
        ->callTableAction(TableDeleteAction::class, $product);

    $this->assertModelMissing($product);
});

it('can edit products from table', function () {
    $product = Product::factory()->create();
    $newData = Product::factory()->make();

    livewire(ProductResource\Pages\ListProducts::class)
        ->callTableAction(EditAction::class, $product, data: [
            'name' => $newData->name,
            'price' => $newData->price,
        ])
        ->assertHasNoTableActionErrors();

    expect($product->refresh())
        ->name->toBe($newData->name)
        ->price->toBe($newData->price);
});
