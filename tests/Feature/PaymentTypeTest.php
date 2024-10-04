<?php

use App\Filament\Resources\PaymentTypeResource;
use App\Models\PaymentType;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

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

it('can render the payment type columns', function () {
    PaymentType::factory()->count(10)->create();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->assertCanRenderTableColumn('name');
});

it('can search payment types by name', function () {
    $paymentTypes = PaymentType::factory()->count(10)->create();

    $name = $paymentTypes->first()->name;

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($paymentTypes->where('name', $name))
        ->assertCanNotSeeTableRecords($paymentTypes->where('name', '!=', $name));
});

it('can sort payment types by name', function () {
    $paymentTypes = PaymentType::factory()->count(10)->create();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($paymentTypes->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($paymentTypes->sortByDesc('name'), inOrder: true);
});

it('can bulk delete the payment types from table', function () {
    $paymentTypes = PaymentType::factory()->count(10)->create();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->callTableBulkAction(DeleteBulkAction::class, $paymentTypes);

    foreach ($paymentTypes as $paymentType) {
        $this->assertModelMissing($paymentType);
    }
});

it('can delete the payment types from table', function () {
    $paymentType = PaymentType::factory()->create();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->callTableAction(TableDeleteAction::class, $paymentType);

    $this->assertModelMissing($paymentType);
});

it('can edit the payment types from table', function () {
    $paymentType = PaymentType::factory()->create();
    $newData = PaymentType::factory()->make();

    livewire(PaymentTypeResource\Pages\ListPaymentTypes::class)
        ->callTableAction(EditAction::class, $paymentType, data: [
            'name' => $newData->name,
        ])
        ->assertHasNoTableActionErrors();

    expect($paymentType->refresh())
        ->name->toBe($newData->name);
});
