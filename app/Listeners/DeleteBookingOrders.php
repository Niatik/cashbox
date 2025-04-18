<?php

namespace App\Listeners;

use App\Events\BookingDeleting;

class DeleteBookingOrders
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(BookingDeleting $event): void
    {
        $orders = $event->booking->orders()->get();
        foreach ($orders as $order) {
            $order->delete();
        }
    }
}
