<?php

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(OrderResource::getUrl('index'))->assertSuccessful();
});

it('can list orders', function () {
    $orders = Order::factory()
        ->count(10)
        ->create(['order_date' => now(tz: 'Etc/GMT-5')]);

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

it('can render page for creating the Order', function () {
    $this->get(OrderResource::getUrl('create'))->assertSuccessful();
});

it('can create the Order', function () {
    $user = User::find(auth()->user()->id);
    $employee_id = $user->employee->id;
    $newData = Order::factory()->make();
    $price = $newData->price->price;
    $people_number = $newData->people_number;
    $service_time = $newData->price_item->time_item;
    $sum = $price * $people_number * $service_time;

    livewire(OrderResource\Pages\CreateOrder::class)
        ->goToWizardStep(1)
        ->assertWizardCurrentStep(1)
        ->fillForm([
            'price_id' => $newData->price_id,
            'price_item_id' => $newData->price_item_id,
            'social_media_id' => $newData->social_media_id,
            'people_number' => $newData->people_number,
            'customer_id' => $newData->customer_id,
        ])
        ->assertFormSet([
            'order_date' => now()->format('Y-m-d'),
            'sum' => $sum,
            'payment' => [
                'payment_cash_amount' => $sum,
                'payment_cashless_amount' => 0,
            ],
        ])
        ->goToNextWizardStep()
        ->assertHasNoFormErrors()
        ->assertWizardCurrentStep(2)
        ->assertHasNoFormErrors()
        ->call('create');

    $this->assertDatabaseHas(Order::class, [
        'order_date' => now()->format('Y-m-d'),
        'price_id' => $newData->price_id,
        'price_item_id' => $newData->price_item_id,
        'social_media_id' => $newData->social_media_id,
        'people_number' => $newData->people_number,
        'sum' => $sum * 100,
        'customer_id' => $newData->customer_id,
        'employee_id' => $employee_id,
    ]);

    $this->assertDatabaseHas(Payment::class, [
        'order_id' => Order::latest()->first()->id,
        'payment_cash_amount' => $sum * 100,
        'payment_cashless_amount' => 0,
        'payment_date' => now()->format('Y-m-d'),
    ]);
});

it('can validate input to create the Order', function () {
    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'price_id' => null,
            'price_item_id' => null,
            'social_media_id' => null,
            'people_number' => null,
            'customer_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'price_id' => 'required',
            'price_item_id' => 'required',
            'social_media_id' => 'required',
            'people_number' => 'required',
        ]);
});

it('can validate input payment to create the Order', function () {
    $newData = Order::factory()->make();
    $price = $newData->price->price;
    $people_number = $newData->people_number;
    $service_time = $newData->price_item->time_item;
    $sum = $price * $people_number * $service_time;

    livewire(OrderResource\Pages\CreateOrder::class)
        ->fillForm([
            'price_id' => $newData->price_id,
            'price_item_id' => $newData->price_item_id,
            'social_media_id' => $newData->social_media_id,
            'people_number' => $newData->people_number,
            'customer_id' => $newData->customer_id,
            'payment' => [
                'payment_cash_amount' => $sum - 200,
                'payment_cashless_amount' => 2000,
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'payment.payment_cash_amount' => 'The total amount of payments does not match the order amount',
            'payment.payment_cashless_amount' => 'The total amount of payments does not match the order amount',
        ]);
});

it('can render page for editing the Order ', function () {
    $this->get(OrderResource::getUrl('edit', [
        'record' => Order::factory()->create(['order_date' => now(tz: 'Etc/GMT-5')]),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Order', function () {
    $order_time_value = now();
    $order_time_string = $order_time_value->format('H:i:s');
    $order_time_string_tz = $order_time_value->tz('Etc/GMT-5')->format('H:i:s');
    $order = Order::factory()->create([
        'order_date' => now(),
        'order_time' => $order_time_string,
    ]);

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormFieldExists('order_date')
        ->assertFormFieldExists('order_time')
        ->assertFormFieldExists('price_id')
        ->assertFormFieldExists('price_item_id')
        ->assertFormFieldExists('social_media_id')
        ->assertFormFieldExists('people_number')
        ->assertFormFieldExists('sum')
        ->assertFormFieldExists('customer_id')
        ->assertFormFieldExists('employee_id')
        ->assertFormSet([
            'order_time' => $order_time_string_tz,
            'price_id' => $order->price_id,
            'price_item_id' => $order->price_item_id,
            'social_media_id' => $order->social_media_id,
            'people_number' => $order->people_number,
            'sum' => $order->sum,
            'customer_id' => $order->customer_id,
            'employee_id' => $order->employee_id,
        ]);
});

it('can save edited Order', function () {
    $order = order::factory()->create();
    $newData = order::factory()->make();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'order_time' => $newData->order_time,
            'price_id' => $newData->price_id,
            'price_item_id' => $newData->price_item_id,
            'social_media_id' => $newData->social_media_id,
            'people_number' => $newData->people_number,
            'customer_id' => $newData->customer_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->order_date->toBe($order->order_date)
        ->order_time->toBe($order->order_time)
        ->price_id->toBe($newData->price_id)
        ->price_item_id->toBe($newData->price_item_id)
        ->social_media_id->toBe($newData->social_media_id)
        ->people_number->toBe($newData->people_number)
        ->sum->toBe($newData->sum)
        ->customer_id->toBe($newData->customer_id);
});

it('can validate input to edit the Order', function () {
    $order = Order::factory()->create();

    livewire(OrderResource\Pages\EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'order_time' => null,
            'price_id' => null,
            'price_item_id' => null,
            'social_media_id' => null,
            'people_number' => null,
            'customer_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['order_time' => 'required'])
        ->assertHasFormErrors(['price_id' => 'required'])
        ->assertHasFormErrors(['price_item_id' => 'required'])
        ->assertHasFormErrors(['social_media_id' => 'required'])
        ->assertHasFormErrors(['people_number' => 'required']);
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
        ->assertCanNotRenderTableColumn('order_date')
        ->assertCanRenderTableColumn('order_time')
        ->assertCanRenderTableColumn('price.name')
        ->assertCanRenderTableColumn('price_item.name_item')
        ->assertCanRenderTableColumn('people_number')
        ->assertCanRenderTableColumn('sum')
        ->assertCanRenderTableColumn('customer.name');
});

it('can search orders by time', function () {
    $orders = Order::factory()->count(10)->create();

    $time = $orders->first()->order_time;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($time)
        ->assertCanSeeTableRecords($orders->where('order_time', $time))
        ->assertCanNotSeeTableRecords($orders->where('order_time', '!=', $time));
});

it('can search orders by price name', function () {
    $orders = Order::factory()->count(10)->create();

    $price = $orders->first()->price->name;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($price)
        ->assertCanSeeTableRecords($orders->where('price.name', $price))
        ->assertCanNotSeeTableRecords($orders->where('price.name', '!=', $price));
});

it('can search orders by customer name', function () {
    $orders = Order::factory()->count(10)->create();

    $customer = $orders->first()->customer->name;

    livewire(OrderResource\Pages\ListOrders::class)
        ->searchTable($customer)
        ->assertCanSeeTableRecords($orders->where('customer.name', $customer))
        ->assertCanNotSeeTableRecords($orders->where('customer.name', '!=', $customer));
});

it('can sort orders by order time', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('order_time')
        ->assertCanSeeTableRecords($orders->sortBy('order_time'), inOrder: true)
        ->sortTable('order_time', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('order_time'), inOrder: true);
});

it('can sort orders by price name', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('price.name')
        ->assertCanSeeTableRecords($orders->sortBy('price.name'), inOrder: true)
        ->sortTable('price.name', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('price.name'), inOrder: true);
});

it('can sort orders by price item', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('price_item.name_item')
        ->assertCanSeeTableRecords($orders->sortBy('price_item.name_item'), inOrder: true)
        ->sortTable('price_item.name_item', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('price_item.name_item'), inOrder: true);
});

it('can sort orders by people number', function () {
    $orders = Order::factory()->count(10)->create();

    livewire(OrderResource\Pages\ListOrders::class)
        ->sortTable('people_number')
        ->assertCanSeeTableRecords($orders->sortBy('people_number'), inOrder: true)
        ->sortTable('people_number', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('people_number'), inOrder: true);
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
            'order_date' => $newData->order_date,
            'order_time' => $newData->order_time,
            'price_id' => $newData->price_id,
            'price_item_id' => $newData->price_item_id,
            'social_media_id' => $newData->social_media_id,
            'people_number' => $newData->people_number,
        ])
        ->assertHasNoTableActionErrors();

    expect($order->refresh())
        ->order_date->toBe($order->order_date)
        ->order_time->toBe($order->order_time)
        ->price_id->toBe($newData->price_id)
        ->price_item_id->toBe($newData->price_item_id)
        ->social_media_id->toBe($newData->social_media_id)
        ->people_number->toBe($newData->people_number)
        ->sum->toBe($newData->sum);
});

it('filters orders by current date in GMT-5 timezone', function () {
    // Create orders for different dates
    $previousOrders = Order::factory()->count(2)->create(['order_date' => now(tz: 'Etc/GMT-5')->subDay()]);
    $todayOrders = Order::factory()->count(3)->create(['order_date' => now(tz: 'Etc/GMT-5')]);
    $futureOrders = Order::factory()->count(2)->create(['order_date' => now(tz: 'Etc/GMT-5')->addDay()]);

    livewire(OrderResource\Pages\ListOrders::class)
        ->assertCanSeeTableRecords($todayOrders)
        ->assertCanNotSeeTableRecords($futureOrders)
        ->assertCanNotSeeTableRecords($previousOrders)
        ->assertCountTableRecords(3);
});
