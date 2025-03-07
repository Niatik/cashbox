<?php

namespace App\Listeners;

use App\Models\Salary;
use Illuminate\Support\Facades\DB;

class SalaryUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct(Salary $salary) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $salary = $event->salary;
        $date = $salary->salary_date;
        $amount = $salary->salary_amount * 100;
        if ($salary->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cash_salary', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->decrement('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_salary', $amount);
        }
    }
}
