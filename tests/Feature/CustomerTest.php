<?php

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});


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
