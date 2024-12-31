<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PriceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingPriceItem = PriceItem::factory()->create();

        return [
            'booking_date' => now(tz: 'Etc/GMT-5'),
            'booking_time' => now(tz: 'Etc/GMT-5'),
            'booking_price_items' => [
                [
                    'price_id' => $bookingPriceItem->price->id,
                    'price_item_id' => $bookingPriceItem->id,
                    'people_number' => 1,
                    'name_item' => $bookingPriceItem->name_item,
                    'people_item' => 2,
                ],
            ],
            'sum' => 0,
            'prepayment' => 0,
            'employee_id' => Employee::factory(),
            'customer_id' => Customer::factory(),
        ];
    }
}
