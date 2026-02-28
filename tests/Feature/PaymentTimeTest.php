<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_time_is_automatically_saved_on_creation(): void
    {
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

        $this->assertNotNull($payment->payment_time);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_time' => $payment->payment_time->format('H:i:s'),
        ]);
    }
}
