<?php

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});


it('can render page', function () {
    $this->get(PaymentResource::getUrl('index'))->assertSuccessful();
});


it('can list payments', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->assertCanSeeTableRecords($payments);
});


it('can render page for creating the Payment', function () {
    $this->get(PaymentResource::getUrl('create'))->assertSuccessful();
});


it('can create the Payment', function () {
    $newData = Payment::factory()->make();

    livewire(PaymentResource\Pages\CreatePayment::class)
        ->fillForm([
            'order_id' => $newData->order_id,
            'payment_type_id' => $newData->payment_type_id,
            'payment_date' => $newData->payment_date,
            'payment_amount' => $newData->payment_amount,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Payment::class, [
        'order_id' => $newData->order_id,
        'payment_type_id' => $newData->payment_type_id,
        'payment_date' => $newData->payment_date,
        'payment_amount' => $newData->payment_amount,
    ]);
});


it('can validate input to create the Payment', function () {
    livewire(PaymentResource\Pages\CreatePayment::class)
        ->fillForm([
            'order_id' => null,
            'payment_type_id' => null,
            'payment_date' => null,
            'payment_amount' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'order_id' => 'required',
            'payment_type_id' => 'required',
            'payment_date' => 'required',
            'payment_amount' => 'required',
        ]);
});

it('can render page for editing the Payment', function () {
    $this->get(PaymentResource::getUrl('edit', [
        'record' => Payment::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Payment', function () {
    $payment = Payment::factory()->create();

    livewire(PaymentResource\Pages\EditPayment::class, [
        'record' => $payment->getRouteKey(),
    ])
        ->assertFormFieldExists('order_id')
        ->assertFormFieldExists('payment_type_id')
        ->assertFormFieldExists('payment_date')
        ->assertFormFieldExists('payment_amount')
        ->assertFormSet([
            'order_id' => $payment->order_id,
            'payment_type_id' => $payment->payment_type_id,
            'payment_date' => $payment->payment_date,
            'payment_amount' => $payment->payment_amount,
        ]);
});

it('can save edited Payment', function () {
    $payment = Payment::factory()->create();
    $newData = Payment::factory()->make();

    livewire(PaymentResource\Pages\EditPayment::class, [
        'record' => $payment->getRouteKey(),
    ])
        ->fillForm([
            'order_id' => $newData->order_id,
            'payment_type_id' => $newData->payment_type_id,
            'payment_date' => $newData->payment_date,
            'payment_amount' => $newData->payment_amount,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($payment->refresh())
        ->order_id->toBe($newData->order_id)
        ->payment_type_id->toBe($newData->payment_type_id)
        ->payment_date->toBe($newData->payment_date)
        ->payment_amount->toBe($newData->payment_amount);
});


it('can validate input to edit the PaymentType', function () {
    $payment = Payment::factory()->create();

    livewire(PaymentResource\Pages\EditPayment::class, [
        'record' => $payment->getRouteKey(),
    ])
        ->fillForm([
            'order_id' => null,
            'payment_type_id' => null,
            'payment_date' => null,
            'payment_amount' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'order_id' => 'required',
            'payment_type_id' => 'required',
            'payment_date' => 'required',
            'payment_amount' => 'required',
        ]);
});


it('can delete the Payment', function () {
    $payment = Payment::factory()->create();

    livewire(PaymentResource\Pages\EditPayment::class, [
        'record' => $payment->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($payment);
});
