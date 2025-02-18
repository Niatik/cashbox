<?php

namespace App\Services;

use App\Models\CashReport;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Salary;
use Carbon\Carbon;

class CashReportService
{
    private function getMorningCashBalance($date)
    {
        $incomeMorningCash = Payment::whereDate('payment_date', '<', $date)->sum('payment_cash_amount');
        $expenseMorningCash = Expense::whereDate('expense_date', '<', $date)->where('is_cash', true)->sum('expense_amount');
        $salaryMorningCash = Salary::whereDate('salary_date', '<', $date)->where('is_cash', true)->sum('salary_amount');

        return $incomeMorningCash - $expenseMorningCash - $salaryMorningCash;
    }

    private function getCashIncome($date)
    {
        return Payment::whereDate('payment_date', $date)->sum('payment_cash_amount');
    }

    private function getCashlessIncome($date)
    {
        return Payment::whereDate('payment_date', $date)->sum('payment_cashless_amount');
    }

    private function getCashExpense($date)
    {
        return Expense::whereDate('expense_date', $date)
            ->where('is_cash', true)
            ->sum('expense_amount');
    }

    private function getCashlessExpense($date)
    {
        return Expense::whereDate('expense_date', $date)
            ->where('is_cash', false)
            ->sum('expense_amount');
    }

    private function getCashSalary($date)
    {
        return Salary::whereDate('salary_date', $date)
            ->where('is_cash', true)
            ->sum('salary_amount');
    }

    private function getCashlessSalary($date)
    {
        return Salary::whereDate('salary_date', $date)
            ->where('is_cash', false)
            ->sum('salary_amount');
    }

    public function calculateAndSaveDailyData(): void
    {
        $minIncomeDate = Payment::min('payment_date');
        $maxIncomeDate = Payment::max('payment_date');
        $minExpenseDate = Expense::min('expense_date');
        $maxExpenseDate = Expense::max('expense_date');
        $minSalaryDate = Salary::min('salary_date');
        $maxSalaryDate = Salary::max('salary_date');

        $minDate = Carbon::parse(min($minIncomeDate, $minExpenseDate, $minSalaryDate));
        $maxDate = Carbon::parse(max($maxIncomeDate, $maxExpenseDate, $maxSalaryDate));

        for ($date = $minDate; $date <= $maxDate; $date->addDay()) {
            $morningBalanceCash = $this->getMorningCashBalance($date);
            $cashIncome = $this->getCashIncome($date);
            $cashlessIncome = $this->getCashlessIncome($date);
            $cashExpense = $this->getCashExpense($date);
            $cashlessExpense = $this->getCashlessExpense($date);
            $cashSalary = $this->getCashSalary($date);
            $cashlessSalary = $this->getCashlessSalary($date);

            CashReport::whereDate('date', $date)->delete();

            CashReport::create([
                'date' => $date,
                'morning_cash_balance' => $morningBalanceCash / 100,
                'cash_income' => $cashIncome / 100,
                'cashless_income' => $cashlessIncome / 100,
                'cash_expense' => $cashExpense / 100,
                'cashless_expense' => $cashlessExpense / 100,
                'cash_salary' => $cashSalary / 100,
                'cashless_salary' => $cashlessSalary / 100,
            ]);
        }
    }
}
