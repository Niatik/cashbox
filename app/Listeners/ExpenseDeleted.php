<?php

namespace App\Listeners;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseDeleted
{
    /**
     * Create the event listener.
     */
    public function __construct(Expense $expense) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $expense = $event->expense;
        $date = $expense->expense_date;
        $amount = $expense->expense_amount * 100;
        if ($expense->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->decrement('cash_expense', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->increment('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->decrement('cashless_expense', $amount);
        }
    }
}
