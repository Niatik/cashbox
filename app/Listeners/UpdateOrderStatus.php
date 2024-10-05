<?php

namespace App\Listeners;

use App\Models\Payment;

class UpdateOrderStatus
{
    /**
     * Create the event listener.
     */
    public function __construct(Payment $payment)
    {
        $amount = $payment->payment_amount;
        $sum = $payment->order->sum;
        $sum -= $amount;
        if ($sum <= 0) {
            $payment->order->status = 'completed';
            $payment->order->save();
        }
    }
}
