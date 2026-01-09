<?php

namespace App\Listeners;

use App\Events\OrderDeleting;

class DeleteOrderPayments
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderDeleting $event): void
    {
        // Delete all payments associated with the order
        $payments = $event->order->payments()->get();
        foreach ($payments as $payment) {
            $payment->delete();
        }
    }
}
