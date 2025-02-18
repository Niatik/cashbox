<?php

namespace App\Listeners;

use App\Models\CashReport;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseCreated
{
    /**
     * Create the event listener.
     */
    public function __construct(Expense $expense)
    {
        $date = $expense->expense_date;
        $amount = $expense->expense_amount;
        if ($expense->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cash_expense', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->decrement('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_expense', $amount);
        }

        $cashReports = DB::table('cash_reports')->whereDate('date', $date)->count();
        if ($cashReports == 0) {
            $previousCashReport = DB::table('cash_reports')->whereDate('date', '<', $date)->orderBy('date', 'desc')->first();
            if ($expense->is_cash) {
                CashReport::create([
                    'date' => $date,
                    'morning_cash_balance' => $previousCashReport->morning_cash_balance,
                    'cash_income' => 0,
                    'cashless_income' => 0,
                    'cash_expense' => $amount,
                    'cashless_expense' => 0,
                    'cash_salary' => 0,
                    'cashless_salary' => 0,
                ]);
            } else {
                CashReport::create([
                    'date' => $date,
                    'morning_cash_balance' => $previousCashReport->morning_cash_balance,
                    'cash_income' => 0,
                    'cashless_income' => 0,
                    'cash_expense' => 0,
                    'cashless_expense' => $amount,
                    'cash_salary' => 0,
                    'cashless_salary' => 0,
                ]);
            }
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
