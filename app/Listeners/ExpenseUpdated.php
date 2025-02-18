<?php

namespace App\Listeners;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct(Expense $expense)
    {
        $date = $expense->expense_date;
        $amount = $expense->expense_amount * 100;
        if ($expense->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cash_expense', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->decrement('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_expense', $amount);
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
