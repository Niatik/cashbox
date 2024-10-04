<?php

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

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
        'payment_amount' => $newData->payment_amount * 100,
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

it('can render payment columns', function () {
    Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->assertCanRenderTableColumn('order.id')
        ->assertCanRenderTableColumn('payment_type.name')
        ->assertCanRenderTableColumn('payment_date')
        ->assertCanRenderTableColumn('payment_amount');
});

it('can search payments by date', function () {
    $payments = Payment::factory()->count(10)->create();

    $date = $payments->first()->payment_date;

    livewire(PaymentResource\Pages\ListPayments::class)
        ->searchTable($date)
        ->assertCanSeeTableRecords($payments->where('payment_date', $date))
        ->assertCanNotSeeTableRecords($payments->where('payment_date', '!=', $date));
});

it('can search payments by order id', function () {
    $payments = Payment::factory()->count(10)->create();

    $orderId = $payments->first()->order->id;

    livewire(PaymentResource\Pages\ListPayments::class)
        ->searchTable($orderId)
        ->assertCanSeeTableRecords($payments->where('order.id', $orderId))
        ->assertCanNotSeeTableRecords($payments->where('order.id', '!=', $orderId));
});

it('can search payments by payment type', function () {
    $payments = Payment::factory()->count(10)->create();

    $paymentType = $payments->first()->payment_type->name;

    livewire(PaymentResource\Pages\ListPayments::class)
        ->searchTable($paymentType)
        ->assertCanSeeTableRecords($payments->where('payment_type.name', $paymentType))
        ->assertCanNotSeeTableRecords($payments->where('payment_type.name', '!=', $paymentType));
});

it('can search payments by payment amount', function () {
    $payments = Payment::factory()->count(10)->create();

    $amount = $payments->first()->payment_amount;

    livewire(PaymentResource\Pages\ListPayments::class)
        ->searchTable($amount)
        ->assertCanSeeTableRecords($payments->where('payment_amount', $amount))
        ->assertCanNotSeeTableRecords($payments->where('payment_amount', '!=', $amount));
});

it('can sort payments by date', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->sortTable('payment_date')
        ->assertCanSeeTableRecords($payments->sortBy('payment_date'), inOrder: true)
        ->sortTable('payment_date', 'desc')
        ->assertCanSeeTableRecords($payments->sortByDesc('payment_date'), inOrder: true);
});

it('can sort payments by order id', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->sortTable('order.id')
        ->assertCanSeeTableRecords($payments->sortBy('order.id'), inOrder: true)
        ->sortTable('order.id', 'desc')
        ->assertCanSeeTableRecords($payments->sortByDesc('order.id'), inOrder: true);
});

it('can sort payments by payment type', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->sortTable('payment_type.name')
        ->assertCanSeeTableRecords($payments->sortBy('payment_type.name'), inOrder: true)
        ->sortTable('payment_type.name', 'desc')
        ->assertCanSeeTableRecords($payments->sortByDesc('payment_type.name'), inOrder: true);
});

it('can sort payments by payment amount', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->sortTable('payment_amount')
        ->assertCanSeeTableRecords($payments->sortBy('payment_amount'), inOrder: true)
        ->sortTable('payment_amount', 'desc')
        ->assertCanSeeTableRecords($payments->sortByDesc('payment_amount'), inOrder: true);
});

it('can bulk delete payments from table', function () {
    $payments = Payment::factory()->count(10)->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->callTableBulkAction(DeleteBulkAction::class, $payments);

    foreach ($payments as $payment) {
        $this->assertModelMissing($payment);
    }
});

it('can delete payment from table', function () {
    $payment = Payment::factory()->create();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->callTableAction(TableDeleteAction::class, $payment);

    $this->assertModelMissing($payment);
});

it('can edit payments from table', function () {
    $payment = Payment::factory()->create();
    $newData = Payment::factory()->make();

    livewire(PaymentResource\Pages\ListPayments::class)
        ->callTableAction(EditAction::class, $payment, data: [
            'order_id' => $newData->order_id,
            'payment_date' => $newData->payment_date,
            'payment_type_id' => $newData->payment_type_id,
            'payment_amount' => $newData->payment_amount,
        ])
        ->assertHasNoTableActionErrors();

    expect($payment->refresh())
        ->order_id->toBe($newData->order_id)
        ->payment_date->toBe($newData->payment_date)
        ->payment_type_id->toBe($newData->payment_type_id)
        ->payment_amount->toBe($newData->payment_amount);
});
