<?php

namespace App\Listeners;

use App\Events\BookingUpdated;
use App\Models\Order;
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
            $oldOrders = Order::query()
                ->with('payments')
                ->where('booking_id', $booking->id)
                ->orderBy('id')
                ->get();
            $newOrders = $this->createOrders($booking);

            $this->reassignPayments($oldOrders, $newOrders);
            $this->createPrepaymentsForOrdersWithoutPayments($newOrders);
            $oldOrders->each->delete();
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

    private function reassignPayments(Collection $oldOrders, Collection $newOrders): void
    {
        $remainingOldOrders = $oldOrders->values();
        $remainingNewOrders = $newOrders->values();

        foreach ($oldOrders as $oldOrder) {
            $newOrder = $remainingNewOrders->first(fn (Order $newOrder): bool => $newOrder->price_id === $oldOrder->price_id
                && $newOrder->price_item_id === $oldOrder->price_item_id);

            if (! $newOrder) {
                continue;
            }

            $this->movePayments($oldOrder, $newOrder);
            $remainingOldOrders = $remainingOldOrders->reject(fn (Order $order): bool => $order->is($oldOrder))->values();
            $remainingNewOrders = $remainingNewOrders->reject(fn (Order $order): bool => $order->is($newOrder))->values();
        }

        foreach ($remainingOldOrders as $index => $oldOrder) {
            $newOrder = $remainingNewOrders->get($index);

            if ($newOrder) {
                $this->movePayments($oldOrder, $newOrder);
            }
        }
    }

    private function movePayments(Order $oldOrder, Order $newOrder): void
    {
        $oldOrder->payments->each(function ($payment) use ($newOrder): void {
            $payment->payable()->associate($newOrder);

            if ($payment->order_id !== null) {
                $payment->order_id = $newOrder->id;
            }

            $payment->save();
        });
    }

    private function createPrepaymentsForOrdersWithoutPayments(Collection $orders): void
    {
        $orders->each(function (Order $order): void {
            $prepayment = $order->options['prepayment'] ?? 0;

            if ($prepayment <= 0 || $order->payments()->exists()) {
                return;
            }

            $isCash = (bool) ($order->options['is_cash'] ?? false);

            $order->payments()->create([
                'payment_date' => now()->timezone('Etc/GMT-5')->format('Y-m-d'),
                'payment_time' => now()->timezone('Etc/GMT-5')->format('H:i:s'),
                'payment_cash_amount' => $isCash ? $prepayment : 0,
                'payment_cashless_amount' => $isCash ? 0 : $prepayment,
            ]);
        });
    }

    private function createOrders($booking): Collection
    {
        $bookingDate = $booking->booking_date;
        $customer = $booking->customer_id;
        $employee = $booking->employee_id;
        $prices = $booking->booking_price_items;
        $orders = collect();

        foreach ($prices as $price) {
            $bookingTime = $price['booking_time'];
            $price_id = $price['price_id'];
            $price_item_id = $price['price_item_id'];
            $people_number = $price['people_number'] ?? 0;
            $people_item = $price['people_item'];
            $prepayment = $price['prepayment_price_item'] ?? 0;
            $isCash = (bool) ($price['is_cash'] ?? false);

            $priceValue = Price::find($price_id)->price;
            $factor = PriceItem::find($price_item_id)->factor;

            $people_calc = intval($people_number);
            if ($people_calc == 0) {
                $people_calc = 1;
            }

            $people_save = $people_number;
            if ($people_item > 1) {
                $people_save = $people_item;
            }

            $net_sum = $people_calc * $factor * $priceValue;
            $sum = $net_sum - $prepayment;

            $order = Order::withoutEvents(function () use ($bookingDate, $bookingTime, $price_id, $price_item_id, $people_save, $sum, $net_sum, $employee, $customer, $prepayment, $isCash, $booking) {
                return Order::create([
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
            });
            $orders->push($order);
        }

        return $orders;
    }
}
