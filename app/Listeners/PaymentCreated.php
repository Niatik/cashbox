<?php

namespace App\Listeners;

use App\Models\CashReport;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class PaymentCreated
{
    /**
     * Create the event listener.
     */
    public function __construct(Payment $payment)
    {
        $date = $payment->payment_date;
        $cash_amount = $payment->payment_cash_amount * 100;
        $cashless_amount = $payment->payment_cashless_amount * 100;

        DB::table('cash_reports')->whereDate('date', $date)->increment('cash_income', $cash_amount);
        DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_income', $cashless_amount);
        DB::table('cash_reports')->whereDate('date', '>', $date)->increment('morning_cash_balance', $cash_amount);

        $cashReports = DB::table('cash_reports')->whereDate('date', $date)->count();
        if ($cashReports == 0) {
            $previousCashReport = DB::table('cash_reports')->whereDate('date', '<', $date)->orderBy('date', 'desc')->first();
            CashReport::create([
                'date' => $date,
                'morning_cash_balance' => $previousCashReport->morning_cash_balance / 100,
                'cash_income' => $payment->payment_cash_amount,
                'cashless_income' => $payment->payment_cashless_amount,
                'cash_expense' => 0,
                'cashless_expense' => 0,
                'cash_salary' => 0,
                'cashless_salary' => 0,
            ]);
        }
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
    }
}
