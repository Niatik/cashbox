<?php

namespace App\Listeners;

use App\Events\ProductOrderDeleting;

class DeleteProductOrderPayments
{
    public function handle(ProductOrderDeleting $event): void
    {
        foreach ($event->productOrder->payments as $payment) {
            $payment->delete();
        }
    }
}
