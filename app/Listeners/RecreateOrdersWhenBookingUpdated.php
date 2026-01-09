<?php

namespace App\Listeners;

use App\Events\BookingUpdated;
use App\Models\Order;
use App\Models\Price;
use App\Models\PriceItem;
use Illuminate\Support\Facades\DB;

class RecreateOrdersWhenBookingUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(BookingUpdated $event): void
    {
        $booking = $event->booking;

        $orders = Order::where('booking_id', $booking->id)->get();

        try {
            DB::beginTransaction();


            foreach ($orders as $order) {
                $order->payments->each(function ($payment) {
                    $payment->delete();
                });
                $order->delete();
            }

            $bookingDate = $booking->booking_date;
            $customer = $booking->customer_id;
            $employee = $booking->employee_id;
            $prices = $booking->booking_price_items;

            foreach ($prices as $price) {
                $bookingTime = $price['booking_time'];
                $price_id = $price['price_id'];
                $price_item_id = $price['price_item_id'];
                $people_number = $price['people_number'] ?? 0;
                $people_item = $price['people_item'];
                $prepayment = $price['prepayment_price_item'];
                $isCash = $price['is_cash'];

                $price = Price::find($price_id)->price;
                $factor = PriceItem::find($price_item_id)->factor;

                $people_calc = intval($people_number);
                if ($people_calc == 0) {
                    $people_calc = 1;
                }

                $people_save = $people_number;
                if ($people_item > 1) {
                    $people_save = $people_item;
                }

                $net_sum = $people_calc * $factor * $price;
                $sum = $net_sum - $prepayment;

                Order::create([
                    'order_date' => $bookingDate,
                    'order_time' => $bookingTime,
                    'price_id' => $price_id,
                    'price_item_id' => $price_item_id,
                    'social_media_id' => 1,
                    'people_number' => $people_save,
                    'sum' => $sum,
                    'net_sum' => $net_sum,
                    'employee_id' => $employee,
                    'customer_id' => $customer,
                    'options' => [
                        'prepayment' => $prepayment,
                        'is_cash' => $isCash,
                    ],
                    'is_paid' => false,
                    'booking_id' => $booking->id,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
