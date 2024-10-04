<?php

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(CustomerResource::getUrl('index'))->assertSuccessful();
});

it('can list customers', function () {
    $customers = Customer::factory()->count(10)->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->assertCanSeeTableRecords($customers);
});

it('can render page for creating the Customer', function () {
    $this->get(CustomerResource::getUrl('create'))->assertSuccessful();
});

it('can create the Customer', function () {
    $newData = Customer::factory()->make();

    livewire(CustomerResource\Pages\CreateCustomer::class)
        ->fillForm([
            'name' => $newData->name,
            'phone' => $newData->phone,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, [
        'name' => $newData->name,
        'phone' => $newData->phone,
    ]);
});

it('can validate input to create the Customer', function () {
    livewire(CustomerResource\Pages\CreateCustomer::class)
        ->fillForm([
            'name' => null,
            'phone' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'phone' => 'required']);
});

it('can render page for editing the Customer ', function () {
    $this->get(CustomerResource::getUrl('edit', [
        'record' => Customer::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Customer', function () {
    $customer = Customer::factory()->create();

    livewire(CustomerResource\Pages\EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $customer->name,
            'phone' => $customer->phone,
        ]);
});

it('can save edited Customer', function () {
    $customer = Customer::factory()->create();
    $newData = Customer::factory()->make();

    livewire(CustomerResource\Pages\EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'phone' => $newData->phone,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($customer->refresh())
        ->name->toBe($newData->name)
        ->phone->toBe($newData->phone);
});

it('can validate input to edit the Customer', function () {
    $customer = Customer::factory()->create();

    livewire(CustomerResource\Pages\EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
            'phone' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'phone' => 'required',
        ]);
});

it('can delete the Customer', function () {
    $customer = Customer::factory()->create();

    livewire(CustomerResource\Pages\EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($customer);
});

it('can render the customer columns', function () {
    Customer::factory()->count(10)->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('phone');
});

it('can search customers by name', function () {
    $customers = Customer::factory()->count(10)->create();

    $name = $customers->first()->name;

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($customers->where('name', $name))
        ->assertCanNotSeeTableRecords($customers->where('name', '!=', $name));
});

it('can search customers by phone', function () {
    $customers = Customer::factory()->count(10)->create();

    $phone = $customers->first()->phone;

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->searchTable($phone)
        ->assertCanSeeTableRecords($customers->where('phone', $phone))
        ->assertCanNotSeeTableRecords($customers->where('phone', '!=', $phone));
});

it('can sort customers by name', function () {
    $customers = Customer::factory()->count(10)->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($customers->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('name'), inOrder: true);
});

it('can sort customers by phone', function () {
    $customers = Customer::factory()->count(10)->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->sortTable('phone')
        ->assertCanSeeTableRecords($customers->sortBy('phone'), inOrder: true)
        ->sortTable('phone', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('phone'), inOrder: true);
});

it('can bulk delete the customers from table', function () {
    $customers = Customer::factory()->count(10)->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $customers);

    foreach ($customers as $customer) {
        $this->assertModelMissing($customer);
    }
});

it('can delete the customers from table', function () {
    $customer = Customer::factory()->create();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->callTableAction(TableDeleteAction::class, $customer);

    $this->assertModelMissing($customer);
});

it('can edit the customers from table', function () {
    $customer = Customer::factory()->create();
    $newData = Customer::factory()->make();

    livewire(CustomerResource\Pages\ListCustomers::class)
        ->callTableAction(EditAction::class, $customer, data: [
            'name' => $newData->name,
            'phone' => $newData->phone,
        ])
        ->assertHasNoTableActionErrors();

    expect($customer->refresh())
        ->name->toBe($newData->name)
        ->phone->toBe($newData->phone);
});
