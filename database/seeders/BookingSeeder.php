<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Price;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 30 days of historical bookings (3-8 per day) + 10 future bookings.
     * Non-draft bookings trigger automatic Order/Payment creation via events.
     *
     * IMPORTANT:
     * - Booking sum/prepayment use MoneyCast: pass dollar amounts (e.g., 60.00)
     * - booking_price_items.prepayment_price_item stores cents directly (raw value)
     */
    public function run(): void
    {
        $employees = Employee::all();
        $customers = Customer::all();
        $prices = Price::with('priceItems')->get();

        // Available booking times
        $bookingTimes = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00',
            '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00', '19:00:00', '20:00:00'];

        // Historical bookings: 30 days back, 3-8 bookings per day
        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();
            $bookingsPerDay = rand(3, 8);

            for ($i = 0; $i < $bookingsPerDay; $i++) {
                $this->createBooking(
                    date: $date,
                    employees: $employees,
                    customers: $customers,
                    prices: $prices,
                    bookingTimes: $bookingTimes,
                    isDraft: false
                );
            }
        }

        // Future bookings: 10 bookings over next 7 days (mix of drafts and confirmed)
        for ($i = 0; $i < 10; $i++) {
            $daysAhead = rand(1, 7);
            $date = now()->addDays($daysAhead)->toDateString();

            // 30% chance of being a draft
            $isDraft = rand(1, 10) <= 3;

            $this->createBooking(
                date: $date,
                employees: $employees,
                customers: $customers,
                prices: $prices,
                bookingTimes: $bookingTimes,
                isDraft: $isDraft
            );
        }
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Price>  $prices
     * @param  array<string>  $bookingTimes
     */
    private function createBooking(
        string $date,
        $employees,
        $customers,
        $prices,
        array $bookingTimes,
        bool $isDraft
    ): void {
        $employee = $employees->random();
        $customer = $customers->random();

        // 1-3 price items per booking
        $numPriceItems = rand(1, 3);
        $bookingPriceItems = [];
        $totalSum = 0;
        $totalPrepayment = 0;

        $usedTimes = [];

        for ($j = 0; $j < $numPriceItems; $j++) {
            $price = $prices->random();
            $priceItem = $price->priceItems->random();

            // Get a unique booking time
            do {
                $bookingTime = $bookingTimes[array_rand($bookingTimes)];
            } while (in_array($bookingTime, $usedTimes));
            $usedTimes[] = $bookingTime;

            $peopleNumber = rand(1, 5);

            // Calculate item price (price * factor * people)
            // Price uses MoneyCast, so $price->price returns dollars
            // Factor uses ThousandthCast, so $priceItem->factor returns decimal
            $itemPriceDollars = $price->price * $priceItem->factor * $peopleNumber;

            // 40% chance of having prepayment
            $hasPrepayment = rand(1, 10) <= 4;
            // Prepayment is 10-50% of item price, stored as cents in JSON
            $prepaymentCents = $hasPrepayment
                ? (int) round($itemPriceDollars * rand(10, 50) / 100 * 100)
                : 0;

            $bookingPriceItems[] = [
                'booking_time' => $bookingTime,
                'price_id' => $price->id,
                'price_item_id' => $priceItem->id,
                'people_number' => $peopleNumber,
                'prepayment_price_item' => $prepaymentCents, // Cents (raw value, not MoneyCast)
                'is_cash' => (bool) rand(0, 1),
                'name_item' => $priceItem->name_item,
                'people_item' => $peopleNumber,
            ];

            $totalSum += $itemPriceDollars;
            $totalPrepayment += $prepaymentCents / 100; // Convert back to dollars for MoneyCast
        }

        Booking::create([
            'booking_date' => $date,
            'booking_price_items' => $bookingPriceItems,
            'sum' => $totalSum, // MoneyCast: dollar amount
            'prepayment' => $totalPrepayment, // MoneyCast: dollar amount
            'employee_id' => $employee->id,
            'customer_id' => $customer->id,
            'is_draft' => $isDraft,
        ]);
    }
}
