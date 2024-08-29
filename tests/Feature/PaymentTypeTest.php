<?php

use App\Filament\Resources\PaymentTypeResource;
use App\Models\PaymentType;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});


it('can render page', function () {
    $this->get(PaymentTypeResource::getUrl('index'))->assertSuccessful();
});


it('can list payment types', function () {
    $paymentTypes = PaymentType::factory()->count(10)->create();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->assertCanSeeTableRecords($paymentTypes);
});


it('can render page for creating the Payment Type', function () {
    $this->get(PaymentTypeResource::getUrl('create'))->assertSuccessful();
});


it('can create the Payment Type', function () {
    $newData = PaymentType::factory()->make();

    livewire(PaymentTypeResource\Pages\CreatePaymentType::class)
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(PaymentType::class, [
        'name' => $newData->name,
    ]);
});


it('can validate input to create the PaymentType', function () {
    livewire(PaymentTypeResource\Pages\CreatePaymentType::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can render page for editing the PaymentType ', function () {
    $this->get(PaymentTypeResource::getUrl('edit', [
        'record' => PaymentType::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the PaymentType', function () {
    $paymentType = PaymentType::factory()->create();

    livewire(PaymentTypeResource\Pages\EditPaymentType::class, [
        'record' => $paymentType->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $paymentType->name,
        ]);
});

it('can save edited PaymentType', function () {
    $paymentType = PaymentType::factory()->create();
    $newData = PaymentType::factory()->make();

    livewire(PaymentTypeResource\Pages\EditPaymentType::class, [
        'record' => $paymentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($paymentType->refresh())
        ->name->toBe($newData->name);
});


it('can validate input to edit the PaymentType', function () {
    $paymentType = PaymentType::factory()->create();

    livewire(PaymentTypeResource\Pages\EditPaymentType::class, [
        'record' => $paymentType->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});


it('can delete the PaymentType', function () {
    $paymentType = PaymentType::factory()->create();

    livewire(PaymentTypeResource\Pages\EditPaymentType::class, [
        'record' => $paymentType->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($paymentType);
});
