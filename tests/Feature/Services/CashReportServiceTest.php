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
    $report25 = CashReport::whereDate('date', '2023-10-25')->first();
    expect($report25->morning_cash_balance)->toBe(0.00)
        ->and($report25->cash_income)->toBe(100.00)
        ->and($report25->cashless_income)->toBe(0.00)
        ->and($report25->cash_expense)->toBe(50.00)
        ->and($report25->cashless_expense)->toBe(0.00)
        ->and($report25->cash_salary)->toBe(10.00)
        ->and($report25->cashless_salary)->toBe(0.00);

    // Check data for 2023-10-26
    $report26 = CashReport::whereDate('date', '2023-10-26')->first();
    expect($report26->morning_cash_balance)->toBe(40.00)
        ->and($report26->cash_income)->toBe(1000.00)
        ->and($report26->cashless_income)->toBe(500.00)
        ->and($report26->cash_expense)->toBe(500.00)
        ->and($report26->cashless_expense)->toBe(0.00)
        ->and($report26->cash_salary)->toBe(200.00)
        ->and($report26->cashless_salary)->toBe(0.00);

    // Check data for 2023-10-27
    $report27 = CashReport::whereDate('date', '2023-10-27')->first();
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
    expect(CashReport::count('date'))->toBe(1);

    // Act
    $this->cashReportService->calculateAndSaveDailyData();

    // Assert
    expect(CashReport::count())->toBe(1)
        ->and(CashReport::first()->date)->toBe(date('Y-m-d'));
});

it('correctly updates existing reports when payment is created', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000;
    $cashlessAmount = 500;

    Payment::factory()->create(
        [
            'payment_date' => '2025-03-06',
            'payment_cash_amount' => $cashAmount,
            'payment_cashless_amount' => $cashlessAmount,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-06')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-06')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-06')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(4)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(2)
        ->and(CashReport::count())->toBe(7);

    $item = 0;
    foreach ($beforeReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'])
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($reports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'])
            ->and($report->cash_income)->toBe($data[$item]['cash_income'] + $cashAmount)
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'] + $cashlessAmount)
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] + $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

function prepareCashReportData(): array
{
    $data = [
        [
            'date' => '2025-03-02',
            'morning_cash_balance' => 0.00,
            'cash_income' => 1000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 500.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-03',
            'morning_cash_balance' => 500.00,
            'cash_income' => 2000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 500.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-04',
            'morning_cash_balance' => 2500.00,
            'cash_income' => 1000.00,
            'cashless_income' => 500.00,
            'cash_expense' => 2000.00,
            'cashless_expense' => 1000.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-05',
            'morning_cash_balance' => 1500.00,
            'cash_income' => 1500.00,
            'cashless_income' => 1000.00,
            'cash_expense' => 1000.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-06',
            'morning_cash_balance' => 2000.00,
            'cash_income' => 0.00,
            'cashless_income' => 1000.00,
            'cash_expense' => 1000.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-07',
            'morning_cash_balance' => 1000.00,
            'cash_income' => 3000.00,
            'cashless_income' => 1000.00,
            'cash_expense' => 1500.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
        [
            'date' => '2025-03-08',
            'morning_cash_balance' => 2500.00,
            'cash_income' => 2000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 0.00,
            'cashless_expense' => 1000.00,
            'cash_salary' => 0.00,
            'cashless_salary' => 0.00,
        ],
    ];

    foreach ($data as $item) {
        CashReport::factory()->create($item);
    }

    return $data;
}
