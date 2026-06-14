<?php

use App\Events\PaymentCreated;
use App\Events\PaymentDeleted;
use App\Events\PaymentUpdated;
use App\Filament\Resources\ProductOrderResource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(ProductOrderResource::getUrl('index'))->assertSuccessful();
});

it('can list product orders', function () {
    Event::fake();

    $orders = ProductOrder::factory()
        ->count(10)
        ->create(['order_date' => now(tz: 'Etc/GMT-5')]);

    livewire(ProductOrderResource\Pages\ListProductOrders::class)
        ->assertCanSeeTableRecords($orders);
});

it('can render page for creating the ProductOrder', function () {
    $this->get(ProductOrderResource::getUrl('create'))->assertSuccessful();
});

it('can create the ProductOrder', function () {
    $user = User::find(auth()->user()->id);
    $product = Product::factory()->create();
    $newData = ProductOrder::factory()->make([
        'product_id' => $product->id,
        'price' => $product->price,
        'sum' => $product->price * 2,
        'quantity' => 2,
    ]);

    livewire(ProductOrderResource\Pages\CreateProductOrder::class)
        ->goToWizardStep(1)
        ->assertWizardCurrentStep(1)
        ->fillForm([
            'product_id' => $newData->product_id,
            'quantity' => $newData->quantity,
            'customer_id' => $newData->customer_id,
        ])
        ->goToNextWizardStep()
        ->assertHasNoFormErrors()
        ->assertWizardCurrentStep(2)
        ->call('create');

    $this->assertDatabaseHas(ProductOrder::class, [
        'order_date' => now()->format('Y-m-d'),
        'product_id' => $newData->product_id,
        'quantity' => $newData->quantity,
        'customer_id' => $newData->customer_id,
        'employee_id' => $user->employee->id,
    ]);

    $productOrder = ProductOrder::query()->latest('id')->first();
    expect($productOrder)->not->toBeNull();

    $this->assertDatabaseHas(Payment::class, [
        'payable_type' => ProductOrder::class,
        'payable_id' => $productOrder->id,
        'payment_date' => now()->format('Y-m-d'),
    ]);
});

it('can validate input to create the ProductOrder', function () {
    livewire(ProductOrderResource\Pages\CreateProductOrder::class)
        ->fillForm([
            'product_id' => null,
            'quantity' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'product_id' => 'required',
            'quantity' => 'required',
        ]);
});

it('can create ProductOrder without payments', function () {
    $product = Product::factory()->create();
    $newData = ProductOrder::factory()->make([
        'product_id' => $product->id,
        'price' => $product->price,
        'quantity' => 1,
        'sum' => $product->price,
    ]);

    livewire(ProductOrderResource\Pages\CreateProductOrder::class)
        ->goToWizardStep(1)
        ->fillForm([
            'product_id' => $newData->product_id,
            'quantity' => $newData->quantity,
            'customer_id' => $newData->customer_id,
        ])
        ->goToNextWizardStep()
        ->assertHasNoFormErrors()
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ProductOrder::class, [
        'product_id' => $newData->product_id,
    ]);
});

it('can render page for editing the ProductOrder', function () {
    Event::fake();

    $this->get(ProductOrderResource::getUrl('edit', [
        'record' => ProductOrder::factory()->create(['order_date' => now(tz: 'Etc/GMT-5')]),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the ProductOrder', function () {
    Event::fake();

    $order = ProductOrder::factory()->create([
        'order_date' => now(),
    ]);

    livewire(ProductOrderResource\Pages\EditProductOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormFieldExists('order_date')
        ->assertFormFieldExists('order_time')
        ->assertFormFieldExists('product_id')
        ->assertFormFieldExists('quantity')
        ->assertFormFieldExists('sum')
        ->assertFormFieldExists('customer_id')
        ->assertFormFieldExists('employee_id')
        ->assertFormSet([
            'product_id' => $order->product_id,
            'quantity' => $order->quantity,
            'sum' => $order->sum,
            'customer_id' => $order->customer_id,
            'employee_id' => $order->employee_id,
        ]);
});

it('can save edited ProductOrder', function () {
    Event::fake();

    $order = ProductOrder::factory()->create();
    $newProduct = Product::factory()->create();
    $newData = ProductOrder::factory()->make([
        'product_id' => $newProduct->id,
        'price' => $newProduct->price,
        'quantity' => 3,
        'sum' => $newProduct->price * 3,
    ]);

    Payment::create([
        'payable_type' => ProductOrder::class,
        'payable_id' => $order->id,
        'payment_date' => now()->format('Y-m-d'),
        'payment_cash_amount' => $order->sum,
        'payment_cashless_amount' => 0,
    ]);

    livewire(ProductOrderResource\Pages\EditProductOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'order_time' => $newData->order_time,
            'product_id' => $newData->product_id,
            'quantity' => $newData->quantity,
            'customer_id' => $newData->customer_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->product_id->toBe($newData->product_id)
        ->quantity->toBe($newData->quantity)
        ->customer_id->toBe($newData->customer_id);
});

it('can delete the ProductOrder', function () {
    Event::fake();

    $order = ProductOrder::factory()->create();

    livewire(ProductOrderResource\Pages\EditProductOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($order);
});

it('deletes payments when ProductOrder is deleted', function () {
    Event::fake([PaymentCreated::class, PaymentUpdated::class, PaymentDeleted::class]);

    $order = ProductOrder::factory()->create();
    $payment = Payment::create([
        'payable_type' => ProductOrder::class,
        'payable_id' => $order->id,
        'payment_date' => now()->format('Y-m-d'),
        'payment_cash_amount' => $order->sum,
        'payment_cashless_amount' => 0,
    ]);

    $order->delete();

    $this->assertModelMissing($payment);
});

it('can delete product orders from table', function () {
    Event::fake();
    $order = ProductOrder::factory()->create(['order_date' => now(tz: 'Etc/GMT-5')]);

    livewire(ProductOrderResource\Pages\ListProductOrders::class)
        ->callTableAction(TableDeleteAction::class, $order);

    $this->assertModelMissing($order);
});

it('filters product orders by current date in GMT-5 timezone', function () {
    Event::fake();

    $previousOrders = ProductOrder::factory()->count(2)->create(['order_date' => now(tz: 'Etc/GMT-5')->subDay()]);
    $todayOrders = ProductOrder::factory()->count(3)->create(['order_date' => now(tz: 'Etc/GMT-5')]);
    $futureOrders = ProductOrder::factory()->count(2)->create(['order_date' => now(tz: 'Etc/GMT-5')->addDay()]);

    livewire(ProductOrderResource\Pages\ListProductOrders::class)
        ->assertCanSeeTableRecords($todayOrders)
        ->assertCanNotSeeTableRecords($futureOrders)
        ->assertCanNotSeeTableRecords($previousOrders)
        ->assertCountTableRecords(3);
});

it('subtracts discounts from header total', function () {
    Event::fake();

    $orderDate = now(tz: 'Etc/GMT-5')->format('Y-m-d');

    ProductOrder::factory()->create([
        'order_date' => $orderDate,
        'price' => 500,
        'quantity' => 2,
        'sum' => 850,
        'options' => [
            'discount' => 100,
            'additional_discount' => 50,
        ],
    ]);

    ProductOrder::factory()->create([
        'order_date' => $orderDate,
        'price' => 500,
        'quantity' => 1,
        'sum' => 500,
        'options' => [],
    ]);

    livewire(ProductOrderResource\Pages\ListProductOrders::class)
        ->assertSee('1350');
});

it('calculates sum with discounts on create', function () {
    $product = Product::factory()->create(['price' => 1000]);

    livewire(ProductOrderResource\Pages\CreateProductOrder::class)
        ->fillForm([
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'discount' => 100,
                'additional_discount' => 50,
            ],
        ])
        ->assertFormSet([
            'sum' => 850,
            'net_sum' => 1000,
        ]);
});
