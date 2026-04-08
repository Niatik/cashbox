<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RateRatio;
use App\Models\SalaryWorkSession;
use App\Models\WorkSession;
use Illuminate\Support\Facades\DB;

class WorkSessionService
{
    /**
     * Calculate balance salary from previous SalaryWorkSessions for the employee.
     * Balance = sum of (income - expense - salary_amount - salary_amount_cashless) from previous sessions.
     */
    public function calculateBalanceSalary(WorkSession $workSession): float
    {
        return SalaryWorkSession::query()
            ->whereHas('workSession', fn ($q) => $q
                ->where('employee_id', $workSession->employee_id)
                ->where('date', '<', $workSession->date))
            ->get()
            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount - $s->salary_amount_cashless);
    }

    /**
     * Calculate income total: salary from SalaryRate + bonus from RateRatio.
     * Bonus is based on the sum of payments for orders on the session date after session start time.
     */
    public function calculateIncomeTotal(WorkSession $workSession): float
    {
        $sessionStart = $workSession->time;

        $orders = Order::query()
            ->where('order_date', $workSession->date)
            ->where('order_time', '>=', $sessionStart)
            ->get();

        $paymentSum = 0;
        foreach ($orders as $order) {
            $paymentSum += $order->payments()
                ->sum(DB::raw('payment_cash_amount + payment_cashless_amount'));
        }

        $salary = $workSession->salaryRate?->salary ?? 0;

        $ratioBonus = 0;
        if ($workSession->rate_id) {
            $matchingRatio = RateRatio::query()
                ->where('rate_id', $workSession->rate_id)
                ->where('ratio_to', '>=', $paymentSum / 100)
                ->where('ratio_from', '<=', $paymentSum / 100)
                ->first();

            if ($matchingRatio) {
                $ratioBonus = $matchingRatio->ratio;
            }
        }

        return $salary + $ratioBonus;
    }

    /**
     * Calculate expense total: sum of all expenseWorkSessions amounts.
     */
    public function calculateExpenseTotal(WorkSession $workSession): float
    {
        return $workSession->expenseWorkSessions->sum(fn ($item) => (float) ($item->amount ?? 0));
    }

    /**
     * Calculate salary total: balance + income - expense.
     */
    public function calculateSalaryTotal(WorkSession $workSession): float
    {
        $balance = $this->calculateBalanceSalary($workSession);
        $income = $this->calculateIncomeTotal($workSession);
        $expense = $this->calculateExpenseTotal($workSession);

        return $balance + $income - $expense;
    }
}
