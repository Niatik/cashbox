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
    Payment::withoutEvents(function () {
        Payment::factory()->create(['payment_date' => '2023-10-26', 'payment_cash_amount' => 1000, 'payment_cashless_amount' => 500]);
        Payment::factory()->create(['payment_date' => '2023-10-27', 'payment_cash_amount' => 2000, 'payment_cashless_amount' => 1000]);
        Payment::factory()->create(['payment_date' => '2023-10-25', 'payment_cash_amount' => 100, 'payment_cashless_amount' => 0]);
    });
    Expense::withoutEvents(function () {
        Expense::factory()->create(['expense_date' => '2023-10-26', 'expense_amount' => 500, 'is_cash' => true]);
        Expense::factory()->create(['expense_date' => '2023-10-27', 'expense_amount' => 1000, 'is_cash' => false]);
        Expense::factory()->create(['expense_date' => '2023-10-25', 'expense_amount' => 50, 'is_cash' => true]);
    });
    Salary::withoutEvents(function () {
        Salary::factory()->create(['salary_date' => '2023-10-26', 'salary_amount' => 200, 'is_cash' => true]);
        Salary::factory()->create(['salary_date' => '2023-10-27', 'salary_amount' => 400, 'is_cash' => false]);
        Salary::factory()->create(['salary_date' => '2023-10-25', 'salary_amount' => 10, 'is_cash' => true]);
    });

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
    Payment::withoutEvents(function () {
        Payment::factory()->create(['payment_date' => date('Y-m-d'), 'payment_cash_amount' => 1000, 'payment_cashless_amount' => 500]);
    });
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

it('correctly creates report when payment is created on new date', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000.00;
    $cashlessAmount = 500.00;

    Payment::factory()->create(
        [
            'payment_date' => '2025-03-09',
            'payment_cash_amount' => $cashAmount,
            'payment_cashless_amount' => $cashlessAmount,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe($cashAmount)
            ->and($report->cashless_income)->toBe($cashlessAmount)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'] + $cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
});

it('correctly creates report when payment is created on non existing date between reports', function () {
    $data = prepareCashReportData();
    $data[] = [
        'morning_cash_balance' => 4500.00,
        'cash_income' => 1000.00,
        'cashless_income' => 0.00,
        'cash_expense' => 1000.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 1000.00,
        'cashless_salary' => 50.00,
    ];
    CashReport::factory()->create(
        [
            'date' => '2025-03-10',
            'morning_cash_balance' => 4500.00,
            'cash_income' => 1000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 1000.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 1000.00,
            'cashless_salary' => 50.00,
        ]
    );
    $cashAmount = 1000.00;
    $cashlessAmount = 500.00;

    Payment::factory()->create(
        [
            'payment_date' => '2025-03-09',
            'payment_cash_amount' => $cashAmount,
            'payment_cashless_amount' => $cashlessAmount,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe($cashAmount)
            ->and($report->cashless_income)->toBe($cashlessAmount)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
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

it('correctly creates report when payment is created and there are still no reports at all', function () {
    $cashAmount = 1000.00;
    $cashlessAmount = 500.00;

    Payment::factory()->create(
        [
            'payment_date' => '2025-03-09',
            'payment_cash_amount' => $cashAmount,
            'payment_cashless_amount' => $cashlessAmount,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(0)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(2);

    foreach ($reports as $report) {
        expect($report->morning_cash_balance)->toBe(0.00)
            ->and($report->cash_income)->toBe($cashAmount)
            ->and($report->cashless_income)->toBe($cashlessAmount)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
});

it('correctly updates report when payment is deleted', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $payment = Payment::where('payment_date', '2025-03-04')->first();
    $cashAmount = $payment->payment_cash_amount;
    $cashlessAmount = $payment->payment_cashless_amount;
    $payment->delete();

    $beforeReports = CashReport::whereDate('date', '<', '2025-03-04')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-04')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-04')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(2)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(4)
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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'] - $cashAmount)
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'] - $cashlessAmount)
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when payment is updated to a higher amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $payment = Payment::where('payment_date', '2025-03-04')->first();
    $cashAmount = $payment->payment_cash_amount;
    $cashlessAmount = $payment->payment_cashless_amount;
    $payment->payment_cash_amount = $cashAmount + 1500;
    $payment->payment_cashless_amount = $cashlessAmount + 500;
    $payment->save();

    $beforeReports = CashReport::whereDate('date', '<', '2025-03-04')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-04')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-04')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(2)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(4)
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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'] + 1500)
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'] + 500)
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] + 1500)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when payment is updated to a lower amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $payment = Payment::where('payment_date', '2025-03-04')->first();
    $cashAmount = $payment->payment_cash_amount;
    $cashlessAmount = $payment->payment_cashless_amount;
    $payment->payment_cash_amount = $cashAmount - 800;
    $payment->payment_cashless_amount = $cashlessAmount - 200;
    $payment->save();

    $beforeReports = CashReport::whereDate('date', '<', '2025-03-04')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-04')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-04')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(2)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(4)
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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'] - 800)
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'] - 200)
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - 800)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when cash expense is deleted', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $expense = Expense::where('expense_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $expense->expense_amount;
    $expense->delete();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'] - $cashAmount)
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

it('correctly updates report when cash salary is deleted', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData(isExpense: false);
    $salary = Salary::where('salary_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $salary->salary_amount;
    $salary->delete();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'] - $cashAmount)
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

it('correctly updates report when expense is updated to a higher amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $expense = Expense::where('expense_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $expense->expense_amount;
    $expense->expense_amount = $cashAmount + 1500;
    $expense->save();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'] + 1500)
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - 1500)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when expense is updated to a lower amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData();
    $expense = Expense::where('expense_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $expense->expense_amount;
    $expense->expense_amount = $cashAmount - 500;
    $expense->save();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'] - 500)
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] + 500)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when salary is updated to a higher amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData(isExpense: false);
    $salary = Salary::where('salary_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $salary->salary_amount;
    $salary->salary_amount = $cashAmount + 1500;
    $salary->save();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'] + 1500)
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - 1500)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates report when salary is updated to a lower amount', function () {
    $data = prepareCashReportData();
    preparePaymentData();
    prepareExpenseOrSalaryData(isExpense: false);
    $salary = Salary::where('salary_date', '2025-03-06')->where('is_cash', true)->first();
    $cashAmount = $salary->salary_amount;
    $salary->salary_amount = $cashAmount - 500;
    $salary->save();

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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'] - 500)
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] + 500)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly updates existing reports when expense is created', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000;

    Expense::factory()->create(
        [
            'expense_date' => '2025-03-06',
            'expense_amount' => $cashAmount,
            'is_cash' => true,
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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'] + $cashAmount)
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly creates report when expense is created on new date', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000.00;

    Expense::factory()->create(
        [
            'expense_date' => '2025-03-09',
            'expense_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe($cashAmount)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
});

it('correctly creates report when expense is created on non existing date between reports', function () {
    $data = prepareCashReportData();
    $data[] = [
        'morning_cash_balance' => 4500.00,
        'cash_income' => 1000.00,
        'cashless_income' => 0.00,
        'cash_expense' => 1000.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 1000.00,
        'cashless_salary' => 50.00,
    ];
    CashReport::factory()->create(
        [
            'date' => '2025-03-10',
            'morning_cash_balance' => 4500.00,
            'cash_income' => 1000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 1000.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 1000.00,
            'cashless_salary' => 50.00,
        ]
    );
    $cashAmount = 1000.00;

    Expense::factory()->create(
        [
            'expense_date' => '2025-03-09',
            'expense_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe($cashAmount)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly creates report when expense is created and there are still no reports at all', function () {
    $cashAmount = 1000.00;

    Expense::factory()->create(
        [
            'expense_date' => '2025-03-09',
            'expense_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(0)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(2);

    foreach ($reports as $report) {
        expect($report->morning_cash_balance)->toBe(0.00)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe($cashAmount)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe(-$cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
});

it('correctly updates existing reports when salary is created', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000;

    Salary::factory()->create(
        [
            'salary_date' => '2025-03-06',
            'salary_amount' => $cashAmount,
            'is_cash' => true,
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
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'] + $cashAmount)
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly creates report when salary is created on new date', function () {
    $data = prepareCashReportData();
    $cashAmount = 1000.00;

    Salary::factory()->create(
        [
            'salary_date' => '2025-03-09',
            'salary_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe($cashAmount)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
    }
});

it('correctly creates report when salary is created on non existing date between reports', function () {
    $data = prepareCashReportData();
    $data[] = [
        'morning_cash_balance' => 4500.00,
        'cash_income' => 1000.00,
        'cashless_income' => 0.00,
        'cash_expense' => 1000.00,
        'cashless_expense' => 0.00,
        'cash_salary' => 1000.00,
        'cashless_salary' => 50.00,
    ];
    CashReport::factory()->create(
        [
            'date' => '2025-03-10',
            'morning_cash_balance' => 4500.00,
            'cash_income' => 1000.00,
            'cashless_income' => 0.00,
            'cash_expense' => 1000.00,
            'cashless_expense' => 0.00,
            'cash_salary' => 1000.00,
            'cashless_salary' => 50.00,
        ]
    );
    $cashAmount = 1000.00;

    Salary::factory()->create(
        [
            'salary_date' => '2025-03-09',
            'salary_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(7)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(9);

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
        expect($report->morning_cash_balance)->toBe($data[$item - 1]['morning_cash_balance'])
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe($cashAmount)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe($data[$item]['morning_cash_balance'] - $cashAmount)
            ->and($report->cash_income)->toBe($data[$item]['cash_income'])
            ->and($report->cashless_income)->toBe($data[$item]['cashless_income'])
            ->and($report->cash_expense)->toBe($data[$item]['cash_expense'])
            ->and($report->cashless_expense)->toBe($data[$item]['cashless_expense'])
            ->and($report->cash_salary)->toBe($data[$item]['cash_salary'])
            ->and($report->cashless_salary)->toBe($data[$item]['cashless_salary']);
        $item++;
    }
});

it('correctly creates report when salary is created and there are still no reports at all', function () {
    $cashAmount = 1000.00;

    Salary::factory()->create(
        [
            'salary_date' => '2025-03-09',
            'salary_amount' => $cashAmount,
            'is_cash' => true,
        ]
    );
    $beforeReports = CashReport::whereDate('date', '<', '2025-03-09')->orderBy('date')->get();
    $reports = CashReport::whereDate('date', '2025-03-09')->orderBy('date')->get();
    $afterReports = CashReport::whereDate('date', '>', '2025-03-09')->orderBy('date')->get();

    expect(count($beforeReports))->toBe(0)
        ->and(count($reports))->toBe(1)
        ->and(count($afterReports))->toBe(1)
        ->and(CashReport::count())->toBe(2);

    foreach ($reports as $report) {
        expect($report->morning_cash_balance)->toBe(0.00)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe($cashAmount)
            ->and($report->cashless_salary)->toBe(0.00);
    }
    foreach ($afterReports as $report) {
        expect($report->morning_cash_balance)->toBe(-$cashAmount)
            ->and($report->cash_income)->toBe(0.00)
            ->and($report->cashless_income)->toBe(0.00)
            ->and($report->cash_expense)->toBe(0.00)
            ->and($report->cashless_expense)->toBe(0.00)
            ->and($report->cash_salary)->toBe(0.00)
            ->and($report->cashless_salary)->toBe(0.00);
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
            'cashless_expense' => 500.00,
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

function preparePaymentData(): array
{
    $data = [
        [
            'payment_date' => '2025-03-02',
            'payment_cash_amount' => 700.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-02',
            'payment_cash_amount' => 300.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-03',
            'payment_cash_amount' => 2000.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-04',
            'payment_cash_amount' => 1000.00,
            'payment_cashless_amount' => 500.00,
        ],
        [
            'payment_date' => '2025-03-05',
            'payment_cash_amount' => 500.00,
            'payment_cashless_amount' => 600.00,
        ],
        [
            'payment_date' => '2025-03-05',
            'payment_cash_amount' => 500.00,
            'payment_cashless_amount' => 400.00,
        ],
        [
            'payment_date' => '2025-03-05',
            'payment_cash_amount' => 500.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-06',
            'payment_cash_amount' => 0.00,
            'payment_cashless_amount' => 500.00,
        ],
        [
            'payment_date' => '2025-03-06',
            'payment_cash_amount' => 0.00,
            'payment_cashless_amount' => 500.00,
        ],
        [
            'payment_date' => '2025-03-07',
            'payment_cash_amount' => 2000.00,
            'payment_cashless_amount' => 1000.00,
        ],
        [
            'payment_date' => '2025-03-07',
            'payment_cash_amount' => 1000.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-08',
            'payment_cash_amount' => 1500.00,
            'payment_cashless_amount' => 0.00,
        ],
        [
            'payment_date' => '2025-03-08',
            'payment_cash_amount' => 500.00,
            'payment_cashless_amount' => 0.00,
        ],
    ];

    Payment::withoutEvents(function () use ($data) {
        foreach ($data as $item) {
            Payment::factory()->create($item);
        }
    });

    return $data;
}

function prepareExpenseOrSalaryData(bool $isExpense = true): array
{
    $typeOfModel = $isExpense ? 'expense' : 'salary';
    $data = [
        [
            $typeOfModel.'_date' => '2025-03-02',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-04',
            $typeOfModel.'_amount' => 1000.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-04',
            $typeOfModel.'_amount' => 1000.00,
            'is_cash' => false,
        ],
        [
            $typeOfModel.'_date' => '2025-03-04',
            $typeOfModel.'_amount' => 1000.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-05',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-05',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-06',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => false,
        ],
        [
            $typeOfModel.'_date' => '2025-03-06',
            $typeOfModel.'_amount' => 1000.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-07',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-07',
            $typeOfModel.'_amount' => 800.00,
            'is_cash' => false,
        ],
        [
            $typeOfModel.'_date' => '2025-03-07',
            $typeOfModel.'_amount' => 200.00,
            'is_cash' => true,
        ],
        [
            $typeOfModel.'_date' => '2025-03-08',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => false,
        ],
        [
            $typeOfModel.'_date' => '2025-03-08',
            $typeOfModel.'_amount' => 500.00,
            'is_cash' => false,
        ],
    ];

    if ($isExpense) {
        Expense::withoutEvents(function () use ($data) {
            foreach ($data as $item) {
                Expense::factory()->create($item);
            }
        });
    } else {
        Salary::withoutEvents(function () use ($data) {
            foreach ($data as $item) {
                Salary::factory()->create($item);
            }
        });
    }

    return $data;
}
