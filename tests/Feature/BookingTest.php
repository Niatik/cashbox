<?php

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\CashReport;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PriceItem;
use App\Models\SocialMedia;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    SocialMedia::factory()->count(10)->create();
});

it('can render page of bookings', function () {
    $this->get(BookingResource::getUrl('index'))->assertSuccessful();
});

it('can list bookings', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $bookings = Booking::factory()
        ->count(10)
        ->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->assertCountTableRecords(10)
        ->assertCanSeeTableRecords($bookings);
});

it('can render page for creating the Booking', function () {
    $this->get(BookingResource::getUrl('create'))->assertSuccessful();
});

it('can create the Booking', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $user = User::find(auth()->user()->id);
    $employee_id = $user->employee->id;
    $newData = Booking::factory()->make();

    livewire(BookingResource\Pages\CreateBooking::class)
        ->set('data.booking_price_items', null)
        ->fillForm([
            'booking_date' => $newData->booking_date->format('Y-m-d'),
            'booking_price_items' => [
                [
                    'booking_time' => $newData->booking_price_items[0]['booking_time'],
                    'price_id' => $newData->booking_price_items[0]['price_id'],
                    'price_item_id' => $newData->booking_price_items[0]['price_item_id'],
                    'people_number' => $newData->booking_price_items[0]['people_number'],
                    'name_item' => $newData->booking_price_items[0]['name_item'],
                    'people_item' => $newData->booking_price_items[0]['people_item'],
                ],
            ],
            'prepayment' => $newData->prepayment,
            'customer_id' => $newData->customer_id,
        ])
        ->assertHasNoFormErrors()
        ->call('create');

    $this->assertDatabaseHas(Booking::class, [
        'prepayment' => $newData->prepayment,
        'customer_id' => $newData->customer_id,
        'employee_id' => $employee_id,
    ]);
});

it('can validate input to create the Booking', function () {
    livewire(BookingResource\Pages\CreateBooking::class)
        ->fillForm([
            'booking_date' => null,
            'customer_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'booking_date' => 'required',
        ]);
});

it('clears the price item state when the selected booking service changes', function () {
    $originalPriceItem = PriceItem::factory()->create(['factor' => 2]);
    $replacementPriceItem = PriceItem::factory()->create(['factor' => 3]);

    livewire(BookingResource\Pages\CreateBooking::class)
        ->fillForm([
            'booking_price_items' => [
                [
                    'booking_time' => now()->format('H:i'),
                    'price_id' => $originalPriceItem->price_id,
                    'price_item_id' => $originalPriceItem->id,
                    'people_number' => 1,
                    'name_item' => $originalPriceItem->name_item,
                    'people_item' => 2,
                ],
            ],
        ])
        ->set('data.booking_price_items.0.price_id', $replacementPriceItem->price_id)
        ->assertFormSet([
            'booking_price_items.0.price_item_id' => null,
            'booking_price_items.0.name_item' => '',
            'booking_price_items.0.people_item' => 1,
            'sum' => 0,
            'remaining' => 0,
        ]);
});

it('does not create a booking or order with a price item from another service', function () {
    $selectedPriceItem = PriceItem::factory()->create();
    $foreignPriceItem = PriceItem::factory()->create();

    livewire(BookingResource\Pages\CreateBooking::class)
        ->fillForm([
            'booking_date' => now()->format('Y-m-d'),
            'booking_price_items' => [
                [
                    'booking_time' => now()->format('H:i'),
                    'price_id' => $selectedPriceItem->price_id,
                    'price_item_id' => $foreignPriceItem->id,
                    'people_number' => 1,
                    'name_item' => $foreignPriceItem->name_item,
                    'people_item' => 1,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'booking_price_items.0.price_item_id' => 'exists',
        ]);

    expect(Booking::query()->count())->toBe(0)
        ->and(Order::query()->count())->toBe(0);
});

it('can render page for editing the Booking ', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $this->get(BookingResource::getUrl('edit', [
        'record' => Booking::factory()->create(['booking_date' => now()]),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Booking', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->assertFormFieldExists('booking_date')
        ->assertFormFieldExists('sum')
        ->assertFormFieldExists('prepayment')
        ->assertFormFieldExists('customer_id')
        ->assertFormFieldExists('employee_id')
        ->assertFormSet([
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'prepayment' => $booking->prepayment,
            'sum' => $booking->sum,
            'customer_id' => $booking->customer_id,
            'employee_id' => $booking->employee_id,
        ]);
});

it('can save edited Booking', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $booking = Booking::factory()->create();
    $newData = Booking::factory()->make();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->fillForm([
            'booking_date' => $newData->booking_date,
            'prepayment' => $newData->prepayment,
            'customer_phone' => $booking->customer->phone,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($booking->refresh())
        ->prepayment->toBe($newData->prepayment);
});

it('can validate input to edit the Booking', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->fillForm([
            'booking_date' => null,
            'customer_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['booking_date' => 'required']);
});

it('can delete the Booking', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($booking);
});

it('can render booking columns', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->removeTableFilters()
        ->assertCanRenderTableColumn('booking_date')
        ->assertCanRenderTableColumn('order_time')
        ->assertCanRenderTableColumn('customer_name');
});

it('can sort bookings by booking date', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $bookings = Booking::factory()->count(10)->create(
        [
            'booking_date' => now()->format('Y-m-d'),
        ]
    );

    livewire(BookingResource\Pages\ListBookings::class)
        ->removeTableFilters()
        ->sortTable('booking_date')
        ->assertCanSeeTableRecords($bookings->sortBy('booking_date'), inOrder: true)
        ->sortTable('booking_date', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('booking_date'), inOrder: true);
});

it('can delete bookings from table', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->removeTableFilters()
        ->callTableAction(TableDeleteAction::class, $booking);

    $this->assertModelMissing($booking);
});

it('filters bookings to view future bookings', function () {
    Event::fake();
    Model::unsetEventDispatcher();

    // Create bookings for different dates
    $previousBookings = Booking::factory()->count(2)->create(['booking_date' => now()->subDay()]);
    $todayBookings = Booking::factory()->count(3)->create(['booking_date' => now()]);
    $futureBookings = Booking::factory()->count(2)->create(['booking_date' => now()->addDay()]);

    livewire(BookingResource\Pages\ListBookings::class)
        ->removeTableFilters()
        ->assertCanSeeTableRecords($todayBookings)
        ->assertCanSeeTableRecords($futureBookings)
        ->assertCanNotSeeTableRecords($previousBookings)
        ->assertCountTableRecords(5);
});

it('can change non-draft booking without affect on payments', function () {
    $bookingPriceItem = PriceItem::factory()->create();

    $booking = Booking::factory()->create([
        'booking_date' => now(tz: 'Etc/GMT-5'),
        'booking_price_items' => [
            [
                'booking_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => false,
            ],
        ],
        'sum' => 0,
        'prepayment' => 2000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    assertDatabaseHas('orders', [
        'booking_id' => $booking->id,
    ]);

    $payment_date = now(tz: 'Etc/GMT-5')->format('Y-m-d');
    $payment_time = now(tz: 'Etc/GMT-5')->format('H:i:s');

    assertDatabaseHas('payments', [
        'payment_date' => $payment_date,
        'payment_time' => $payment_time,
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 200000,
    ]);

    $booking->update([
        'booking_date' => now(tz: 'Etc/GMT-5')->addDay(),
    ]);

    assertDatabaseHas('orders', [
        'booking_id' => $booking->id,
    ]);

    assertDatabaseHas('payments', [
        'payment_date' => $payment_date,
        'payment_time' => $payment_time,
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 200000,
    ]);
});

it('does not change payment attributes when booking date is updated', function () {
    // Устанавливаем фиксированное время для теста
    Carbon::setTestNow('2024-01-15 10:30:00');

    $bookingPriceItem = PriceItem::factory()->create();

    $booking = Booking::factory()->create([
        'booking_date' => now(tz: 'Etc/GMT-5'),
        'booking_price_items' => [
            [
                'booking_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => false,
            ],
        ],
        'sum' => 0,
        'prepayment' => 2000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    $payment = Payment::whereHasMorph('payable', [Order::class], function ($query) use ($booking) {
        $query->where('booking_id', $booking->id);
    })->first();

    $paymentDate = $payment->payment_date;
    $paymentTime = $payment->payment_time;
    $payment_cash_amount = $payment->payment_cash_amount;
    $payment_cashless_amount = $payment->payment_cashless_amount;

    // Перематываем время на час вперед
    Carbon::setTestNow('2024-01-15 11:30:00');

    // Изменяем бронирование
    $booking->update([
        'booking_date' => now(tz: 'Etc/GMT-5')->addDay(),
    ]);

    $payment = Payment::whereHasMorph('payable', [Order::class], function ($query) use ($booking) {
        $query->where('booking_id', $booking->id);
    })->first();

    // Проверяем, что атрибуты остались прежними
    expect($payment->payment_date)->toEqual($paymentDate)
        ->and($payment->payment_time)->toEqual($paymentTime)
        ->and($payment->payment_cash_amount)->toEqual($payment_cash_amount)
        ->and($payment->payment_cashless_amount)->toEqual($payment_cashless_amount);

    // Очищаем фиксированное время
    Carbon::setTestNow();
});

it('creates prepayment payment when draft booking with prepayment is published', function () {
    $bookingPriceItem = PriceItem::factory()->create();

    $booking = Booking::factory()->draft()->create([
        'booking_date' => now(tz: 'Etc/GMT-5'),
        'booking_price_items' => [
            [
                'booking_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 3000,
                'people_item' => 2,
                'is_cash' => false,
            ],
        ],
        'sum' => 0,
        'prepayment' => 3000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
    ]);

    expect(Order::where('booking_id', $booking->id)->count())->toBe(0);
    expect(Payment::count())->toBe(0);

    $booking->update(['is_draft' => false]);

    $order = Order::where('booking_id', $booking->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->options['prepayment'])->toEqual(3000);

    assertDatabaseHas('payments', [
        'payable_type' => Order::class,
        'payable_id' => $order->id,
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 300000,
    ]);

    assertDatabaseHas('cash_reports', [
        'date' => now(tz: 'Etc/GMT-5')->format('Y-m-d'),
        'cashless_income' => 300000,
    ]);
});

it('creates prepayment payment when prepayment is added to a published booking', function () {
    $bookingPriceItem = PriceItem::factory()->create();
    $bookingTime = now(tz: 'Etc/GMT-5')->format('H:i:s');

    $booking = Booking::factory()->create([
        'booking_date' => now(tz: 'Etc/GMT-5'),
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 0,
                'people_item' => 2,
                'is_cash' => true,
            ],
        ],
        'sum' => 0,
        'prepayment' => 0,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    expect(Payment::whereHasMorph('payable', [Order::class], function ($query) use ($booking) {
        $query->where('booking_id', $booking->id);
    })->count())->toBe(0);

    $booking->update([
        'prepayment' => 1500,
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 1500,
                'people_item' => 2,
                'is_cash' => true,
            ],
        ],
    ]);

    assertDatabaseHas('payments', [
        'payment_cash_amount' => 150000,
        'payment_cashless_amount' => 0,
    ]);

    assertDatabaseHas('cash_reports', [
        'date' => now(tz: 'Etc/GMT-5')->format('Y-m-d'),
        'cash_income' => 150000,
    ]);
});

it('restores all payments when booking is updated', function () {
    $bookingPriceItem = PriceItem::factory()->create();
    $bookingTime = now(tz: 'Etc/GMT-5')->format('H:i:s');

    $booking = Booking::factory()->create([
        'booking_date' => now(tz: 'Etc/GMT-5'),
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => false,
            ],
        ],
        'sum' => 0,
        'prepayment' => 2000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    $order = Order::where('booking_id', $booking->id)->first();
    $order->payments()->create([
        'payment_date' => now(tz: 'Etc/GMT-5')->format('Y-m-d'),
        'payment_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 5000,
    ]);

    expect($order->payments()->count())->toBe(2);

    $booking->update([
        'booking_date' => now(tz: 'Etc/GMT-5')->addDay(),
    ]);

    $order = Order::where('booking_id', $booking->id)->first();

    expect($order->payments()->count())->toBe(2);
    expect($order->payments->sum(fn ($payment) => $payment->payment_cash_amount + $payment->payment_cashless_amount))->toEqual(7000.0);
});

it('preserves payments and cash reports when a booking service is replaced', function () {
    $bookingPriceItem = PriceItem::factory()->create();
    $replacementPriceItem = PriceItem::factory()->create();
    $bookingDate = now(tz: 'Etc/GMT-5');
    $bookingTime = $bookingDate->format('H:i:s');

    $booking = Booking::factory()->create([
        'booking_date' => $bookingDate,
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => true,
            ],
        ],
        'sum' => 0,
        'prepayment' => 2000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    $oldOrder = Order::where('booking_id', $booking->id)->firstOrFail();
    $oldOrder->payments()->create([
        'payment_date' => $bookingDate->format('Y-m-d'),
        'payment_time' => $bookingTime,
        'payment_cash_amount' => 3000,
        'payment_cashless_amount' => 1000,
    ]);

    $originalPayments = $oldOrder->payments()
        ->orderBy('id')
        ->get()
        ->map(fn (Payment $payment): array => [
            'id' => $payment->id,
            'payment_date' => $payment->payment_date->format('Y-m-d'),
            'payment_time' => $payment->payment_time->format('H:i:s'),
            'payment_cash_amount' => $payment->payment_cash_amount,
            'payment_cashless_amount' => $payment->payment_cashless_amount,
        ]);
    $cashReport = CashReport::whereDate('date', $bookingDate)->firstOrFail();
    $followingCashReport = CashReport::whereDate('date', $bookingDate->copy()->addDay())->firstOrFail();
    $cashIncome = $cashReport->cash_income;
    $cashlessIncome = $cashReport->cashless_income;
    $morningCashBalance = $followingCashReport->morning_cash_balance;

    $booking->update([
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $replacementPriceItem->price->id,
                'price_item_id' => $replacementPriceItem->id,
                'people_number' => 1,
                'name_item' => $replacementPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => true,
            ],
        ],
    ]);

    $replacementOrder = Order::where('booking_id', $booking->id)->firstOrFail();
    $replacementPayments = $replacementOrder->payments()
        ->orderBy('id')
        ->get()
        ->map(fn (Payment $payment): array => [
            'id' => $payment->id,
            'payment_date' => $payment->payment_date->format('Y-m-d'),
            'payment_time' => $payment->payment_time->format('H:i:s'),
            'payment_cash_amount' => $payment->payment_cash_amount,
            'payment_cashless_amount' => $payment->payment_cashless_amount,
        ]);

    expect($replacementOrder)
        ->price_id->toBe($replacementPriceItem->price->id)
        ->price_item_id->toBe($replacementPriceItem->id)
        ->and($replacementPayments->all())->toEqual($originalPayments->all())
        ->and($cashReport->refresh()->cash_income)->toBe($cashIncome)
        ->and($cashReport->cashless_income)->toBe($cashlessIncome)
        ->and($followingCashReport->refresh()->morning_cash_balance)->toBe($morningCashBalance);
});

it('preserves payment type when is_cash is toggled on the booking price item', function () {
    $bookingPriceItem = PriceItem::factory()->create();

    $bookingDate = now(tz: 'Etc/GMT-5');
    $bookingTime = $bookingDate->format('H:i:s');

    $booking = Booking::factory()->create([
        'booking_date' => $bookingDate,
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => false,
            ],
        ],
        'sum' => 0,
        'prepayment' => 2000,
        'employee_id' => Employee::factory(),
        'customer_id' => Customer::factory(),
        'is_draft' => false,
    ]);

    assertDatabaseHas('payments', [
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 200000,
    ]);

    $booking->update([
        'booking_price_items' => [
            [
                'booking_time' => $bookingTime,
                'price_id' => $bookingPriceItem->price->id,
                'price_item_id' => $bookingPriceItem->id,
                'people_number' => 1,
                'name_item' => $bookingPriceItem->name_item,
                'prepayment_price_item' => 2000,
                'people_item' => 2,
                'is_cash' => true,
            ],
        ],
    ]);

    assertDatabaseHas('payments', [
        'payment_cash_amount' => 0,
        'payment_cashless_amount' => 200000,
    ]);
});
