<?php

namespace App\Listeners;

use App\Events\OrderCreated;

class CreatePaymentForOrderPrepayment
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $options = $order->options;
        $amount = $options['prepayment'];
        $isCash = $options['is_cash'];
        if ($amount > 0) {
            $order->payments()->create([
                'payment_cash_amount' => $isCash ? $amount : 0,
                'payment_cashless_amount' => $isCash ? 0 : $amount,
                'payment_date' => now(),
            ]);
        }
    }
}
