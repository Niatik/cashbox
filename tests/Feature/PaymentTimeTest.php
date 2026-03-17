<?php

use App\Models\Order;

it('automatically saves payment time on creation', function () {
    $order = Order::factory()->create([
        'options' => [
            'prepayment' => 0,
            'is_cash' => true,
        ],
    ]);

    $payment = $order->payments()->create([
        'payment_date' => now(),
        'payment_cash_amount' => 1000,
        'payment_cashless_amount' => 0,
    ]);

    expect($payment->payment_time)->not->toBeNull();

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'payment_time' => $payment->payment_time->format('H:i:s'),
    ]);
});
