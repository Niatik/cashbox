<?php

use App\Filament\Resources\WorkSessionResource;
use App\Models\CashReport;
use App\Models\ExpenseWorkSession;
use App\Models\WorkSession;

use function Pest\Livewire\livewire;

it('can render expense work sessions repeater on edit page', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('expenseWorkSessions');
});

it('can create expense work session via repeater', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'expenseWorkSessions' => [
                [
                    'expense_type' => 'Транспорт',
                    'amount' => 150.00,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(ExpenseWorkSession::where('work_session_id', $workSession->id)->count())->toBe(1);

    $expense = ExpenseWorkSession::where('work_session_id', $workSession->id)->first();
    expect($expense->expense_type)->toBe('Транспорт')
        ->and($expense->amount)->toBe(150.00);
});

it('can update expense work session via repeater', function () {
    $workSession = WorkSession::factory()->create();
    $expenseWorkSession = ExpenseWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'expense_type' => 'Еда',
        'amount' => 100 * 100,
    ]);

    $component = livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ]);

    $formState = $component->get('data');
    $repeaterKey = array_key_first($formState['expenseWorkSessions']);

    $component
        ->fillForm([
            'expenseWorkSessions' => [
                $repeaterKey => [
                    'expense_type' => 'Транспорт',
                    'amount' => 200.00,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $workSession->refresh();
    $expenses = $workSession->expenseWorkSessions;
    expect($expenses)->toHaveCount(1)
        ->and($expenses->first()->expense_type)->toBe('Транспорт')
        ->and($expenses->first()->amount)->toBe(200.00);
});

it('can delete expense work session via repeater', function () {
    $workSession = WorkSession::factory()->create();
    ExpenseWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'expense_type' => 'Еда',
        'amount' => 100 * 100,
    ]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'expenseWorkSessions' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(ExpenseWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});

it('creates cash report entry when expense work session is created', function () {
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

    ExpenseWorkSession::create([
        'work_session_id' => $workSession->id,
        'expense_type' => 'Транспорт',
        'amount' => 150.00,
    ]);

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cash_expense)->toBe(150.00);
});

it('updates cash report when expense work session is deleted', function () {
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

    $expense = ExpenseWorkSession::create([
        'work_session_id' => $workSession->id,
        'expense_type' => 'Транспорт',
        'amount' => 150.00,
    ]);

    $expense->delete();

    $report = CashReport::whereDate('date', '2025-01-15')->first();
    expect($report->cash_expense)->toBe(0.00);
});
