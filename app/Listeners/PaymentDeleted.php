<?php

namespace App\Listeners;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class PaymentDeleted
{
    /**
     * Create the event listener.
     */
    public function __construct(Payment $payment)
    {
        $date = $payment->payment_date;
        $cash_amount = $payment->payment_cash_amount * 100;
        $cashless_amount = $payment->payment_cashless_amount * 100;
        DB::table('cash_reports')->whereDate('date', $date)->decrement('cash_income', $cash_amount);
        DB::table('cash_reports')->whereDate('date', $date)->decrement('cashless_income', $cashless_amount);
        DB::table('cash_reports')->whereDate('date', '>', $date)->decrement('morning_cash_balance', $cash_amount);

    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
    }
}
