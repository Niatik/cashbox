<?php

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;


beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});


it('can render page', function () {
    $this->get(OrderResource::getUrl('index'))->assertSuccessful();
});


it('can list orders', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});


it('can render page for creating the Order', function () {
    $this->get(OrderResource::getUrl('create'))->assertSuccessful();
});


it('can create the Order', function () {
    $newData = Order::factory()->make();
    $price = $newData->service->price;
    $people_number = $newData->people_number;
    $time_order = $newData->time_order;
    $sum = $price * $people_number * $time_order;

    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'date_order' => $newData->date_order,
            'service_id' => $newData->service_id,
            'social_media_id' => $newData->social_media_id,
            'time_order' => $newData->time_order,
            'people_number' => $newData->people_number,
            'status' => $newData->status,
        ])
        ->assertFormSet([
            'sum' => $sum,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'date_order' => $newData->date_order,
        'service_id' => $newData->service_id,
        'social_media_id' => $newData->social_media_id,
        'time_order' => $newData->time_order,
        'people_number' => $newData->people_number,
        'status' => $newData->status,
        'sum' => $sum,
    ]);
});


it('can validate input to create the Order', function () {
    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'date_order' => null,
            'service_id' => null,
            'social_media_id' => null,
            'time_order' => null,
            'people_number' => null,
            'status' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'date_order' => 'required',
            'service_id' => 'required',
            'social_media_id' => 'required',
            'time_order' => 'required',
            'people_number' => 'required',
            'status' => 'required',
        ]);
});

it('can render page for editing the Order ', function () {
    $this->get(OrderResource::getUrl('edit', [
        'record' => Order::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormFieldExists('date_order')
        ->assertFormFieldExists('service_id')
        ->assertFormFieldExists('social_media_id')
        ->assertFormFieldExists('time_order')
        ->assertFormFieldExists('people_number')
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('sum')
        ->assertFormSet([
            'date_order' => $order->date_order,
            'service_id' => $order->service_id,
            'social_media_id' => $order->social_media_id,
            'time_order' => $order->time_order,
            'people_number' => $order->people_number,
            'status' => $order->status,
            'sum' => $order->sum,
        ]);
});

it('can save edited Order', function () {
    $order = order::factory()->create();
    $newData = order::factory()->make();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'date_order' => $newData->date_order,
            'service_id' => $newData->service_id,
            'social_media_id' => $newData->social_media_id,
            'time_order' => $newData->time_order,
            'people_number' => $newData->people_number,
            'status' => $newData->status,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->date_order->toBe($newData->date_order)
        ->service_id->toBe($newData->service_id)
        ->social_media_id->toBe($newData->social_media_id)
        ->time_order->toBe($newData->time_order)
        ->people_number->toBe($newData->people_number)
        ->status->toBe($newData->status)
        ->sum->toBe($newData->sum);
});


it('can validate input to edit the Order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'date_order' => null,
            'service_id' => null,
            'social_media_id' => null,
            'time_order' => null,
            'people_number' => null,
            'status' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['date_order' => 'required'])
        ->assertHasFormErrors(['service_id' => 'required'])
        ->assertHasFormErrors(['social_media_id' => 'required'])
        ->assertHasFormErrors(['time_order' => 'required'])
        ->assertHasFormErrors(['people_number' => 'required'])
        ->assertHasFormErrors(['status' => 'required']);
});


it('can delete the Order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($order);
});
