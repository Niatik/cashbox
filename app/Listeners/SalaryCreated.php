<?php

namespace App\Listeners;

use App\Models\CashReport;
use App\Models\Salary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class SalaryCreated
{
    /**
     * Create the event listener.
     */
    public function __construct(Salary $salary)
    {
        $date = $salary->salary_date;
        $amount = $salary->salary_amount;
        if ($salary->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cash_salary', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->decrement('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_salary', $amount);
        }

        $cashReports = DB::table('cash_reports')->whereDate('date', $date)->count();
        if ($cashReports == 0) {
            $previousCashReport = DB::table('cash_reports')->whereDate('date', '<', $date)->orderBy('date', 'desc')->first();
            if ($salary->is_cash) {
                CashReport::create([
                    'date' => $date,
                    'morning_cash_balance' => $previousCashReport->morningBalanceCash,
                    'cash_income' => 0,
                    'cashless_income' => 0,
                    'cash_expense' => 0,
                    'cashless_expense' => 0,
                    'cash_salary' => $amount,
                    'cashless_salary' => 0,
                ]);
            } else {
                CashReport::create([
                    'date' => $date,
                    'morning_cash_balance' => $previousCashReport->morningBalanceCash,
                    'cash_income' => 0,
                    'cashless_income' => 0,
                    'cash_expense' => 0,
                    'cashless_expense' => 0,
                    'cash_salary' => 0,
                    'cashless_salary' => $amount,
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
