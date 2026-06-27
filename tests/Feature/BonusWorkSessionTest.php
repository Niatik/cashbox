<?php

use App\Filament\Resources\WorkSessionResource;
use App\Models\BonusWorkSession;
use App\Models\SalaryWorkSession;
use App\Models\WorkSession;

use function Pest\Livewire\livewire;

it('can render bonus work sessions repeater on edit page', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('bonusWorkSessions');
});

it('can create bonus work session via repeater', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'bonusWorkSessions' => [
                [
                    'amount' => 150.00,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(BonusWorkSession::where('work_session_id', $workSession->id)->count())->toBe(1);

    $bonus = BonusWorkSession::where('work_session_id', $workSession->id)->first();
    expect($bonus->amount)->toBe(150.00)
        ->and($bonus->date)->not->toBeNull()
        ->and($bonus->time)->not->toBeNull();
});

it('can update bonus work session via repeater', function () {
    $workSession = WorkSession::factory()->create();
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);

    $component = livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ]);

    $formState = $component->get('data');
    $repeaterKey = array_key_first($formState['bonusWorkSessions']);

    $component
        ->fillForm([
            'bonusWorkSessions' => [
                $repeaterKey => [
                    'amount' => 200.00,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $workSession->refresh();
    $bonuses = $workSession->bonusWorkSessions;
    expect($bonuses)->toHaveCount(1)
        ->and($bonuses->first()->amount)->toBe(200.00);
});

it('can delete bonus work session via repeater', function () {
    $workSession = WorkSession::factory()->create();
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'bonusWorkSessions' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(BonusWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});

it('calculates salary_work_session bonus as sum of bonus work sessions', function () {
    $workSession = WorkSession::factory()->create();

    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 50.00,
    ]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormSet([
            'salary_work_session.bonus' => 150.0,
        ]);
});

it('recalculates salary_total when bonus work sessions change', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'bonusWorkSessions' => [
                [
                    'amount' => 100.00,
                ],
                [
                    'amount' => 50.00,
                ],
            ],
        ])
        ->assertFormSet(function (array $state): bool {
            $balance = (float) ($state['salary_work_session']['balance_salary'] ?? 0);
            $income = (float) ($state['salary_work_session']['income_total'] ?? 0);
            $expense = (float) ($state['salary_work_session']['expense_total'] ?? 0);
            $bonus = (float) ($state['salary_work_session']['bonus'] ?? 0);
            $salaryTotal = (float) ($state['salary_work_session']['salary_total'] ?? 0);

            return $bonus === 150.0
                && $salaryTotal === $balance + $income - $expense + $bonus;
        });
});

it('shows bonus work sessions in salary section after payment', function () {
    $workSession = WorkSession::factory()->create();

    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 50.00,
    ]);

    SalaryWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'bonus' => 150.00,
    ]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('bonusWorkSessions')
        ->assertFormSet(function (array $state): bool {
            $bonuses = $state['bonusWorkSessions'] ?? [];

            return count($bonuses) === 2
                && collect($bonuses)->sum(fn ($item) => (float) ($item['amount'] ?? 0)) === 150.0;
        });
});

it('saves bonus sum to SalaryWorkSession when salary payment action is called', function () {
    $workSession = WorkSession::factory()->create();

    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 25.00,
    ]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'salary_work_session.income_total' => 100,
            'salary_work_session.expense_total' => 0,
            'salary_work_session.salary_total' => 225,
            'salary_work_session.salary_amount' => 225,
            'salary_work_session.salary_amount_cashless' => 0,
        ])
        ->mountFormComponentAction('zarplata-smeny', 'salary_payment')
        ->callMountedFormComponentAction();

    $salary = SalaryWorkSession::where('work_session_id', $workSession->id)->first();
    expect($salary->bonus)->toBe(125.0);
});

it('deletes bonus work sessions when work session is deleted', function () {
    $workSession = WorkSession::factory()->create();
    BonusWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
        'amount' => 100.00,
    ]);

    $workSession->delete();

    expect(BonusWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});
