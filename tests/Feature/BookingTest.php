<?php

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\SocialMedia;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

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
