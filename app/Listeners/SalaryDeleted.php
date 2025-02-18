<?php

namespace App\Listeners;

use App\Models\Salary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class SalaryDeleted
{
    /**
     * Create the event listener.
     */
    public function __construct(Salary $salary)
    {
        $date = $salary->salary_date;
        $amount = $salary->salary_amount * 100;
        if ($salary->is_cash) {
            DB::table('cash_reports')->whereDate('date', $date)->decrement('cash_salary', $amount);
            DB::table('cash_reports')->whereDate('date', '>', $date)->increment('morning_cash_balance', $amount);
        } else {
            DB::table('cash_reports')->whereDate('date', $date)->increment('cashless_salary', $amount);
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
