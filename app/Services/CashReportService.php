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
        $minIncomeDate = Payment::min('payment_date') ?? Carbon::now();
        $maxIncomeDate = Payment::max('payment_date') ?? Carbon::now();
        $minExpenseDate = Expense::min('expense_date') ?? Carbon::now();
        $maxExpenseDate = Expense::max('expense_date') ?? Carbon::now();
        $minSalaryDate = Salary::min('salary_date') ?? Carbon::now();
        $maxSalaryDate = Salary::max('salary_date') ?? Carbon::now();

        $minDate = Carbon::parse(min($minIncomeDate, $minExpenseDate, $minSalaryDate));
        $maxDate = Carbon::parse(max($maxIncomeDate, $maxExpenseDate, $maxSalaryDate));

        for ($date = $minDate; $date <= $maxDate; $date->addDay()) {
            $strDate = $date->format('Y-m-d');
            $morningBalanceCash = $this->getMorningCashBalance($strDate);
            $cashIncome = $this->getCashIncome($strDate);
            $cashlessIncome = $this->getCashlessIncome($strDate);
            $cashExpense = $this->getCashExpense($strDate);
            $cashlessExpense = $this->getCashlessExpense($strDate);
            $cashSalary = $this->getCashSalary($strDate);
            $cashlessSalary = $this->getCashlessSalary($strDate);

            CashReport::whereDate('date', $strDate)->delete();

            CashReport::create([
                'date' => $strDate,
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

    /**
     * Обновить данные при создании платежа
     */
    public function updateOnPaymentCreated(Payment $payment): void
    {
        $date = $payment->payment_date;
        $cashAmount = $payment->payment_cash_amount;
        $cashlessAmount = $payment->payment_cashless_amount;

        CashReport::whereDate('date', '>', $date)->increment('morning_cash_balance', $cashAmount * 100);
        $existsOnDate = CashReport::whereDate('date', $date)->exists();
        if ($existsOnDate) {
            CashReport::whereDate('date', $date)->increment('cash_income', $cashAmount * 100);
            CashReport::whereDate('date', $date)->increment('cashless_income', $cashlessAmount * 100);
        } else {
            $morningCashBalance = CashReport::whereDate('date', '<', $date)->orderBy('date', 'desc')->first()?->morning_cash_balance ?? 0.00;
            CashReport::create([
                'date' => $date,
                'morning_cash_balance' => $morningCashBalance,
                'cash_income' => $cashAmount,
                'cashless_income' => $cashlessAmount,
                'cash_expense' => 0.00,
                'cashless_expense' => 0.00,
                'cash_salary' => 0.00,
                'cashless_salary' => 0.00,
            ]);
            if (! CashReport::whereDate('date', '>', $date)->exists()) {
                CashReport::create([
                    'date' => Carbon::parse($date)->addDay()->format('Y-m-d'),
                    'morning_cash_balance' => $morningCashBalance + $cashAmount,
                    'cash_income' => 0.00,
                    'cashless_income' => 0.00,
                    'cash_expense' => 0.00,
                    'cashless_expense' => 0.00,
                    'cash_salary' => 0.00,
                    'cashless_salary' => 0.00,
                ]);
            }
        }
    }

    public function updateOnPaymentDeleted(Payment $payment)
    {
        $date = $payment->payment_date;
        $cashAmount = $payment->payment_cash_amount;
        $cashlessAmount = $payment->payment_cashless_amount;
        CashReport::where('date', $date)->decrement('cash_income', $cashAmount * 100);
        CashReport::where('date', $date)->decrement('cashless_income', $cashlessAmount * 100);
        CashReport::where('date', '>', $date)->decrement('morning_cash_balance', $cashAmount * 100);
    }

    public function updateOnPaymentUpdated(Payment $payment)
    {
        $oldCashAmount = $payment->getOriginal('payment_cash_amount');
        $oldCashlessAmount = $payment->getOriginal('payment_cashless_amount');
        $date = $payment->payment_date;
        $cashAmount = $payment->payment_cash_amount;
        $cashlessAmount = $payment->payment_cashless_amount;
        $diffCashAmount = $cashAmount - $oldCashAmount;
        $diffCashlessAmount = $cashlessAmount - $oldCashlessAmount;
        CashReport::where('date', $date)->increment('cash_income', $diffCashAmount * 100);
        CashReport::where('date', $date)->increment('cashless_income', $diffCashlessAmount * 100);
        CashReport::where('date', '>', $date)->increment('morning_cash_balance', $diffCashAmount * 100);
    }

    public function updateOnExpenseDeleted(Expense $expense): void
    {
        $date = $expense->expense_date;
        $cashAmount = $expense->expense_amount;
        $isCash = $expense->is_cash;
        if ($isCash) {
            CashReport::where('date', $date)->decrement('cash_expense', $cashAmount * 100);
            CashReport::where('date', '>', $date)->increment('morning_cash_balance', $cashAmount * 100);
        } else {
            CashReport::where('date', $date)->decrement('cashless_expense', $cashAmount * 100);
        }
    }
}
