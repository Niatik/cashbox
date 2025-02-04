<?php

use App\Filament\Resources\BookingResource;
use App\Listeners\BookingCreated;
use App\Models\Booking;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

use function Pest\Livewire\livewire;

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

/*it('can create the Booking', function () {
    Event::fake([BookingCreated::class]);

    $user = User::find(auth()->user()->id);
    $employee_id = $user->employee->id;
    $newData = Booking::factory()->make();
    $undoRepeaterFake = Repeater::fake();

    $component = livewire(BookingResource\Pages\CreateBooking::class);

    // Сначала установим booking_price_items
    $component->set('data.booking_price_items', [
        [
            'price_id' => $newData->booking_price_items[0]['price_id'],
            'price_item_id' => $newData->booking_price_items[0]['price_item_id'],
            'people_number' => $newData->booking_price_items[0]['people_number'],
            'name_item' => $newData->booking_price_items[0]['name_item'],
            'people_item' => $newData->booking_price_items[0]['people_item'],
        ],
    ]);

    // Затем заполним остальные поля
    $component->fillForm([
        'booking_date' => $newData->booking_date->format('Y-m-d'),
        'booking_time' => $newData->booking_time->tz('Etc/GMT-0')->format('H:i:s'),
        'prepayment' => $newData->prepayment,
        'customer_id' => $newData->customer_id,
    ]);

    $component
        ->assertHasNoFormErrors()
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Booking::class, [
        'booking_date' => $newData->booking_date->format('Y-m-d'),
        'booking_time' => $newData->booking_time->tz('Etc/GMT+5')->format('H:i:s'),
        'prepayment' => $newData->prepayment,
        'customer_id' => $newData->customer_id,
        'employee_id' => $employee_id,
    ]);

    $undoRepeaterFake();
});*/

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
            'booking_time' => $newData->booking_time->tz('Etc/GMT-0')->format('H:i:s'),
            'booking_price_items' => [
                [
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
        'booking_date' => $newData->booking_date->format('Y-m-d'),
        'booking_time' => $newData->booking_time->tz('Etc/GMT+5')->format('H:i:s'),
        'prepayment' => $newData->prepayment,
        'customer_id' => $newData->customer_id,
        'employee_id' => $employee_id,
    ]);
});

it('can validate input to create the Booking', function () {
    livewire(BookingResource\Pages\CreateBooking::class)
        ->fillForm([
            'booking_date' => null,
            'booking_time' => null,
            'customer_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'booking_date' => 'required',
            'booking_time' => 'required',
            'customer_id' => 'required',
        ]);
});

it('can render page for editing the Booking ', function () {
    $this->get(BookingResource::getUrl('edit', [
        'record' => Booking::factory()->create(['booking_date' => now()]),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Booking', function () {
    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->assertFormFieldExists('booking_date')
        ->assertFormFieldExists('booking_time')
        ->assertFormFieldExists('sum')
        ->assertFormFieldExists('prepayment')
        ->assertFormFieldExists('customer_id')
        ->assertFormFieldExists('employee_id')
        ->assertFormSet([
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'booking_time' => $booking->booking_time->tz('Etc/GMT-10')->format('H:i:s'),
            'prepayment' => $booking->prepayment,
            'sum' => $booking->sum,
            'customer_id' => $booking->customer_id,
            'employee_id' => $booking->employee_id,
        ]);
});

it('can save edited Booking', function () {
    $booking = Booking::factory()->create();
    $newData = Booking::factory()->make();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->fillForm([
            'booking_date' => $newData->booking_date,
            'booking_time' => $newData->booking_time,
            'prepayment' => $newData->prepayment,
            'customer_id' => $newData->customer_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($booking->refresh())
        ->booking_date->toBe($booking->booking_date)
        ->booking_time->toBe($booking->booking_time)
        ->prepayment->toBe($newData->prepayment)
        ->sum->toBe($newData->sum)
        ->customer_id->toBe($newData->customer_id);
});

it('can validate input to edit the Booking', function () {
    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->fillForm([
            'booking_date' => null,
            'booking_time' => null,
            'customer_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['booking_date' => 'required'])
        ->assertHasFormErrors(['booking_time' => 'required'])
        ->assertHasFormErrors(['customer_id' => 'required']);
});

it('can delete the Booking', function () {
    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\EditBooking::class, [
        'record' => $booking->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($booking);
});

it('can render booking columns', function () {
    Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->assertCanRenderTableColumn('booking_date')
        ->assertCanRenderTableColumn('booking_time')
        ->assertCanRenderTableColumn('sum')
        ->assertCanRenderTableColumn('prepayment')
        ->assertCanRenderTableColumn('customer.name');
});

it('can search bookings by date', function () {
    $bookings = Booking::factory()->count(10)->create(
        [
            'booking_date' => now()->format('Y-m-d'),
        ]
    );

    $date = $bookings->first()->booking_date;

    livewire(BookingResource\Pages\ListBookings::class)
        ->searchTable($date)
        ->assertCanSeeTableRecords($bookings->where('booking_date', $date))
        ->assertCanNotSeeTableRecords($bookings->where('booking_date', '!=', $date));
});

it('can search bookings by time', function () {
    $bookings = Booking::factory()->count(10)->create(
        [
            'booking_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
        ]
    );

    $time = $bookings->first()->booking_time;

    livewire(BookingResource\Pages\ListBookings::class)
        ->searchTable($time)
        ->assertCanSeeTableRecords($bookings->where('booking_time', $time))
        ->assertCanNotSeeTableRecords($bookings->where('booking_time', '!=', $time));
});

it('can search bookings by customer name', function () {
    $bookings = Booking::factory()->count(10)->create();

    $customer = $bookings->first()->customer->name;

    livewire(BookingResource\Pages\ListBookings::class)
        ->searchTable($customer)
        ->assertCanSeeTableRecords($bookings->where('customer.name', $customer))
        ->assertCanNotSeeTableRecords($bookings->where('customer.name', '!=', $customer));
});

it('can sort orders by booking date', function () {
    $bookings = Booking::factory()->count(10)->create(
        [
            'booking_date' => now()->format('Y-m-d'),
        ]
    );

    livewire(BookingResource\Pages\ListBookings::class)
        ->sortTable('booking_date')
        ->assertCanSeeTableRecords($bookings->sortBy('booking_date'), inOrder: true)
        ->sortTable('booking_date', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('booking_date'), inOrder: true);
});

it('can sort orders by booking time', function () {
    $bookings = Booking::factory()->count(10)->create(
        [
            'booking_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
        ]
    );

    livewire(BookingResource\Pages\ListBookings::class)
        ->sortTable('booking_time')
        ->assertCanSeeTableRecords($bookings->sortBy('booking_time'), inOrder: true)
        ->sortTable('booking_time', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('booking_time'), inOrder: true);
});

it('can sort bookings by sum', function () {
    $bookings = Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->sortTable('sum')
        ->assertCanSeeTableRecords($bookings->sortBy('sum'), inOrder: true)
        ->sortTable('sum', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('sum'), inOrder: true);
});

it('can sort bookings by prepayment', function () {
    $bookings = Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->sortTable('prepayment')
        ->assertCanSeeTableRecords($bookings->sortBy('prepayment'), inOrder: true)
        ->sortTable('prepayment', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('prepayment'), inOrder: true);
});

it('can sort bookings by customer name', function () {
    $bookings = Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->sortTable('customer.name')
        ->assertCanSeeTableRecords($bookings->sortBy('customer.name'), inOrder: true)
        ->sortTable('customer.name', 'desc')
        ->assertCanSeeTableRecords($bookings->sortByDesc('customer.name'), inOrder: true);
});

it('can bulk delete bookings from table', function () {
    $bookings = Booking::factory()->count(10)->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->callTableBulkAction(DeleteBulkAction::class, $bookings);

    foreach ($bookings as $booking) {
        $this->assertModelMissing($booking);
    }
});

it('can delete bookings from table', function () {
    $booking = Booking::factory()->create();

    livewire(BookingResource\Pages\ListBookings::class)
        ->callTableAction(TableDeleteAction::class, $booking);

    $this->assertModelMissing($booking);
});

it('can edit bookings from table', function () {
    $booking = Booking::factory()->create();
    $newData = Booking::factory()->make();

    livewire(BookingResource\Pages\ListBookings::class)
        ->callTableAction(EditAction::class, $booking, data: [
            'booking_date' => $newData->booking_date,
            'booking_time' => $newData->booking_time,
            'prepayment' => $newData->prepayment,
            'customer_id' => $newData->customer_id,
        ])
        ->assertHasNoTableActionErrors();

    expect($booking->refresh())
        ->booking_date->toBe($booking->booking_date)
        ->booking_time->toBe($booking->booking_time)
        ->prepayment->toBe($newData->prepayment)
        ->sum->toBe($newData->sum)
        ->customer_id->toBe($newData->customer_id);
});

it('filters bookings to view future bookings', function () {
    // Create bookings for different dates
    $previousBookings = Booking::factory()->count(2)->create(['booking_date' => now()->subDay()]);
    $todayBookings = Booking::factory()->count(3)->create(['booking_date' => now()]);
    $futureBookings = Booking::factory()->count(2)->create(['booking_date' => now()->addDay()]);

    livewire(BookingResource\Pages\ListBookings::class)
        ->assertCanSeeTableRecords($todayBookings)
        ->assertCanSeeTableRecords($futureBookings)
        ->assertCanNotSeeTableRecords($previousBookings)
        ->assertCountTableRecords(5);
});
