<?php

use App\Models\CashReport;
use App\Models\SalaryWorkSession;
use App\Models\WorkSession;

it('creates cash report entry when cash salary work session is created', function () {
    $workSession = WorkSession::factory()->create([
        'date' => '2025-01-15',
    ]);

    CashReport::create([
        'date' => '2025-01-15',
        'morning_cash_balance' => 1000.00,
        'cash_income' => 0.00,
        'cashless_income' => 0.00,
        'cash_expense' => 0.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 0.00,
        'cashless_salary' => 0.00,
    ]);

    SalaryWorkSession::create([
        'work_session_id' => $workSession->id,
        'income_total' => 5000.00,
        'expense_total' => 1000.00,
        'salary_total' => 2000.00,
        'salary_amount' => 500.00,
        'is_cash' => true,
    ]);

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cash_salary)->toBe(500.00);
});

it('creates cash report entry when cashless salary work session is created', function () {
    $workSession = WorkSession::factory()->create([
        'date' => '2025-01-15',
    ]);

    CashReport::create([
        'date' => '2025-01-15',
        'morning_cash_balance' => 1000.00,
        'cash_income' => 0.00,
        'cashless_income' => 0.00,
        'cash_expense' => 0.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 0.00,
        'cashless_salary' => 0.00,
    ]);

    SalaryWorkSession::create([
        'work_session_id' => $workSession->id,
        'income_total' => 5000.00,
        'expense_total' => 1000.00,
        'salary_total' => 2000.00,
        'salary_amount' => 500.00,
        'is_cash' => false,
    ]);

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cashless_salary)->toBe(500.00)
        ->and($report->cash_salary)->toBe(0.00);
});

it('updates cash report when salary work session is deleted', function () {
    $workSession = WorkSession::factory()->create([
        'date' => '2025-01-15',
    ]);

    CashReport::create([
        'date' => '2025-01-15',
        'morning_cash_balance' => 1000.00,
        'cash_income' => 0.00,
        'cashless_income' => 0.00,
        'cash_expense' => 0.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 0.00,
        'cashless_salary' => 0.00,
    ]);

    $salaryWorkSession = SalaryWorkSession::create([
        'work_session_id' => $workSession->id,
        'income_total' => 5000.00,
        'expense_total' => 1000.00,
        'salary_total' => 2000.00,
        'salary_amount' => 500.00,
        'is_cash' => true,
    ]);

    $salaryWorkSession->delete();

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cash_salary)->toBe(0.00);
});

it('updates cash report when salary work session is updated', function () {
    $workSession = WorkSession::factory()->create([
        'date' => '2025-01-15',
    ]);

    CashReport::create([
        'date' => '2025-01-15',
        'morning_cash_balance' => 1000.00,
        'cash_income' => 0.00,
        'cashless_income' => 0.00,
        'cash_expense' => 0.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 0.00,
        'cashless_salary' => 0.00,
    ]);

    $salaryWorkSession = SalaryWorkSession::create([
        'work_session_id' => $workSession->id,
        'income_total' => 5000.00,
        'expense_total' => 1000.00,
        'salary_total' => 2000.00,
        'salary_amount' => 500.00,
        'is_cash' => true,
    ]);

    $salaryWorkSession->update([
        'salary_amount' => 800.00,
    ]);

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cash_salary)->toBe(800.00);
});
