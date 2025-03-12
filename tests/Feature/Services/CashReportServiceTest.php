<?php

use App\Models\CashReport;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Salary;
use App\Services\CashReportService;

beforeEach(function () {
    $this->cashReportService = new CashReportService;
});

it('calculates and saves daily data', function () {
    // Arrange
    Payment::factory()->create(['payment_date' => '2023-10-26', 'payment_cash_amount' => 1000, 'payment_cashless_amount' => 500]);
    Payment::factory()->create(['payment_date' => '2023-10-27', 'payment_cash_amount' => 2000, 'payment_cashless_amount' => 1000]);
    Expense::factory()->create(['expense_date' => '2023-10-26', 'expense_amount' => 500, 'is_cash' => true]);
    Expense::factory()->create(['expense_date' => '2023-10-27', 'expense_amount' => 1000, 'is_cash' => false]);
    Salary::factory()->create(['salary_date' => '2023-10-26', 'salary_amount' => 200, 'is_cash' => true]);
    Salary::factory()->create(['salary_date' => '2023-10-27', 'salary_amount' => 400, 'is_cash' => false]);
    Payment::factory()->create(['payment_date' => '2023-10-25', 'payment_cash_amount' => 100, 'payment_cashless_amount' => 0]);
    Expense::factory()->create(['expense_date' => '2023-10-25', 'expense_amount' => 50, 'is_cash' => true]);
    Salary::factory()->create(['salary_date' => '2023-10-25', 'salary_amount' => 10, 'is_cash' => true]);

    // Act
    $this->cashReportService->calculateAndSaveDailyData();

    // Assert
    expect(CashReport::count())->toBe(3);

    // Check data for 2023-10-25
    $report25 = CashReport::whereDate('date', '=', '2023-10-25', false)->first();
    expect($report25->morning_cash_balance)->toBe(0.00)
        ->and($report25->cash_income)->toBe(100.00)
        ->and($report25->cashless_income)->toBe(0.00)
        ->and($report25->cash_expense)->toBe(50.00)
        ->and($report25->cashless_expense)->toBe(0.00)
        ->and($report25->cash_salary)->toBe(10.00)
        ->and($report25->cashless_salary)->toBe(0.00);

    // Check data for 2023-10-26
    $report26 = CashReport::whereDate('date', '=', '2023-10-26', false)->first();
    expect($report26->morning_cash_balance)->toBe(40.00)
        ->and($report26->cash_income)->toBe(1000.00)
        ->and($report26->cashless_income)->toBe(500.00)
        ->and($report26->cash_expense)->toBe(500.00)
        ->and($report26->cashless_expense)->toBe(0.00)
        ->and($report26->cash_salary)->toBe(200.00)
        ->and($report26->cashless_salary)->toBe(0.00);

    // Check data for 2023-10-27
    $report27 = CashReport::whereDate('date', '=', '2023-10-27', false)->first();
    expect($report27->morning_cash_balance)->toBe(340.00)
        ->and($report27->cash_income)->toBe(2000.00)
        ->and($report27->cashless_income)->toBe(1000.00)
        ->and($report27->cash_expense)->toBe(0.00)
        ->and($report27->cashless_expense)->toBe(1000.00)
        ->and($report27->cash_salary)->toBe(0.00)
        ->and($report27->cashless_salary)->toBe(400.00);
});

it('correctly deletes existing reports for date', function () {
    // Arrange
    Payment::factory()->create(['payment_date' => date('Y-m-d'), 'payment_cash_amount' => 1000, 'payment_cashless_amount' => 500]);
    $this->cashReportService->calculateAndSaveDailyData();
    expect(CashReport::count('id'))->toBe(1);

    // Act
    $this->cashReportService->calculateAndSaveDailyData();

    // Assert
    expect(CashReport::count())->toBe(1)
        ->and(CashReport::first()->date)->toBe(date('Y-m-d'));
});
