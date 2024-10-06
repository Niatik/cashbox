<?php

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

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
    $customer_id = $newData->customer_id;

    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'date_order' => $newData->date_order,
            'service_id' => $newData->service_id,
            'social_media_id' => $newData->social_media_id,
            'time_order' => $newData->time_order,
            'people_number' => $newData->people_number,
            'status' => $newData->status,
            'customer_id' => $newData->customer_id,
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
        'sum' => $sum * 100,
        'customer_id' => $newData->customer_id,
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
            'customer_id' => null,
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
        ->assertFormFieldExists('customer_id')
        ->assertFormSet([
            'date_order' => $order->date_order,
            'service_id' => $order->service_id,
            'social_media_id' => $order->social_media_id,
            'time_order' => $order->time_order,
            'people_number' => $order->people_number,
            'status' => $order->status,
            'sum' => $order->sum,
            'customer_id' => $order->customer_id,
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
            'customer_id' => $newData->customer_id,
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
        ->sum->toBe($newData->sum)
        ->customer_id->toBe($newData->customer_id);
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
            'customer_id' => null,
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

it('can render order columns', function () {
    Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanRenderTableColumn('date_order')
        ->assertCanRenderTableColumn('service.name')
        ->assertCanRenderTableColumn('time_order')
        ->assertCanRenderTableColumn('people_number')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('sum')
        ->assertCanRenderTableColumn('customer.name');
});

it('can search orders by date', function () {
    $orders = Order::factory()->count(10)->create();

    $date = $orders->first()->date_order;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($date)
        ->assertCanSeeTableRecords($orders->where('date_order', $date))
        ->assertCanNotSeeTableRecords($orders->where('date_order', '!=', $date));
});

it('can search orders by service name', function () {
    $orders = Order::factory()->count(10)->create();

    $service = $orders->first()->service->name;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($service)
        ->assertCanSeeTableRecords($orders->where('service.name', $service))
        ->assertCanNotSeeTableRecords($orders->where('service.name', '!=', $service));
});

it('can search orders by customer name', function () {
    $orders = Order::factory()->count(10)->create();

    $customer = $orders->first()->customer->name;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($customer)
        ->assertCanSeeTableRecords($orders->where('customer.name', $customer))
        ->assertCanNotSeeTableRecords($orders->where('customer.name', '!=', $customer));
});


it('can sort orders by date', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('date_order')
        ->assertCanSeeTableRecords($orders->sortBy('date_order'), inOrder: true)
        ->sortTable('date_order', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('date_order'), inOrder: true);
});

it('can sort orders by service name', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('service.name')
        ->assertCanSeeTableRecords($orders->sortBy('service.name'), inOrder: true)
        ->sortTable('service.name', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('service.name'), inOrder: true);
});

it('can sort orders by time', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('time_order')
        ->assertCanSeeTableRecords($orders->sortBy('time_order'), inOrder: true)
        ->sortTable('time_order', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('time_order'), inOrder: true);
});

it('can sort orders by people number', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('people_number')
        ->assertCanSeeTableRecords($orders->sortBy('people_number'), inOrder: true)
        ->sortTable('people_number', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('people_number'), inOrder: true);
});

it('can sort orders by status', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('status')
        ->assertCanSeeTableRecords($orders->sortBy('status'), inOrder: true)
        ->sortTable('status', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('status'), inOrder: true);
});

it('can sort orders by sum', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('sum')
        ->assertCanSeeTableRecords($orders->sortBy('sum'), inOrder: true)
        ->sortTable('sum', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('sum'), inOrder: true);
});

it('can sort orders by customer name', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('customer.name')
        ->assertCanSeeTableRecords($orders->sortBy('customer.name'), inOrder: true)
        ->sortTable('customer.name', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('customer.name'), inOrder: true);
});

it('can bulk delete orders from table', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->callTableBulkAction(DeleteBulkAction::class, $orders);

    foreach ($orders as $order) {
        $this->assertModelMissing($order);
    }
});

it('can delete orders from table', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->callTableAction(TableDeleteAction::class, $order);

    $this->assertModelMissing($order);
});

it('can edit orders from table', function () {
    $order = Order::factory()->create();
    $newData = Order::factory()->make();

    livewire(OrderResource\Pages\ListOrders::class)
        ->callTableAction(EditAction::class, $order, data: [
            'date_order' => $newData->date_order,
            'service_id' => $newData->service_id,
            'social_media_id' => $newData->social_media_id,
            'time_order' => $newData->time_order,
            'people_number' => $newData->people_number,
            'status' => $newData->status,
        ])
        ->assertHasNoTableActionErrors();

    expect($order->refresh())
        ->date_order->toBe($newData->date_order)
        ->service_id->toBe($newData->service_id)
        ->social_media_id->toBe($newData->social_media_id)
        ->time_order->toBe($newData->time_order)
        ->people_number->toBe($newData->people_number)
        ->status->toBe($newData->status)
        ->sum->toBe($newData->sum);
});
