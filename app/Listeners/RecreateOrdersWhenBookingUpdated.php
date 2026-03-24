<?php

namespace App\Listeners;

use App\Events\BookingUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Price;
use App\Models\PriceItem;
use App\Models\SocialMedia;
use Illuminate\Support\Collection;
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
        $wasDraft = $booking->getOriginal('is_draft');
        $isDraft = $booking->is_draft;

        // Draft remains draft - do nothing
        if ($wasDraft && $isDraft) {
            return;
        }

        // Published becomes draft - delete orders
        if (! $wasDraft && $isDraft) {
            $this->deleteOrdersAndPayments($booking);

            return;
        }

        // Draft is published OR published is updated - recreate orders
        try {
            DB::beginTransaction();
            $savedPayments = $this->saveAndDeleteOrdersAndPayments($booking);

            $this->createOrders($booking, $savedPayments);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function deleteOrdersAndPayments($booking): void
    {
        $orders = Order::where('booking_id', $booking->id)->get();

        foreach ($orders as $order) {
            $order->payments->each(function ($payment) {
                $payment->delete();
            });
            $order->delete();
        }
    }

    private function saveAndDeleteOrdersAndPayments($booking): Collection
    {
        $savedPayments = collect();
        $orders = Order::where('booking_id', $booking->id)->get();

        foreach ($orders as $order) {
            $order->payments->each(function ($payment) use ($order, &$savedPayments) {
                // Сохраняем данные платежа с привязкой к price_id
                $savedPayments->push([
                    'price_id' => $order->price_id,
                    'payment_date' => $payment->payment_date,
                    'payment_time' => $payment->payment_time,
                    'payment_cash_amount' => $payment->payment_cash_amount,
                    'payment_cashless_amount' => $payment->payment_cashless_amount,
                ]);
                $payment->delete();
            });
            $order->delete();
        }

        return $savedPayments;
    }

    private function createOrders($booking, Collection $savedPayments): void
    {
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

            $order = Order::create([
                'order_date' => $bookingDate,
                'order_time' => $bookingTime,
                'price_id' => $price_id,
                'price_item_id' => $price_item_id,
                'social_media_id' => SocialMedia::find(7)?->id ?? SocialMedia::first()->id,
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

            Payment::where('order_id', $order->id)->delete();

            // Восстанавливаем платеж со старыми атрибутами, если он был
            $savedPayment = $savedPayments->firstWhere('price_id', $price_id);

            if ($savedPayment && $prepayment > 0) {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_date' => $savedPayment['payment_date'],
                    'payment_time' => $savedPayment['payment_time'],
                    'payment_cash_amount' => $savedPayment['payment_cash_amount'],
                    'payment_cashless_amount' => $savedPayment['payment_cashless_amount'],
                ]);
            }
        }
    }
}
