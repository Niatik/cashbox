<?php

use App\Models\SalaryWorkSession;
use App\Models\WorkSession;

it('can create a salary work session', function () {
    $salaryWorkSession = SalaryWorkSession::factory()->create();

    expect($salaryWorkSession)->toBeInstanceOf(SalaryWorkSession::class)
        ->and($salaryWorkSession->work_session_id)->not->toBeNull()
        ->and($salaryWorkSession->income_total)->toBeFloat()
        ->and($salaryWorkSession->expense_total)->toBeFloat()
        ->and($salaryWorkSession->salary_total)->toBeFloat()
        ->and($salaryWorkSession->salary_amount)->toBeFloat()
        ->and($salaryWorkSession->salary_amount_cashless)->toBeFloat();
});

it('belongs to a work session', function () {
    $workSession = WorkSession::factory()->create();
    $salaryWorkSession = SalaryWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
    ]);

    expect($salaryWorkSession->workSession)
        ->toBeInstanceOf(WorkSession::class)
        ->and($salaryWorkSession->workSession->id)->toBe($workSession->id);
});

it('deletes salary work sessions when work session is deleted', function () {
    $workSession = WorkSession::factory()->create();
    SalaryWorkSession::factory()->create(['work_session_id' => $workSession->id]);

    $workSession->delete();

    expect(SalaryWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});

it('can create cash-only salary work session', function () {
    $salaryWorkSession = SalaryWorkSession::factory()->cash()->create();

    expect($salaryWorkSession->salary_amount)->toBeGreaterThan(0)
        ->and($salaryWorkSession->salary_amount_cashless)->toBe(0.0);
});

it('can create cashless-only salary work session', function () {
    $salaryWorkSession = SalaryWorkSession::factory()->cashless()->create();

    expect($salaryWorkSession->salary_amount)->toBe(0.0)
        ->and($salaryWorkSession->salary_amount_cashless)->toBeGreaterThan(0);
});

it('can create mixed salary work session', function () {
    $salaryWorkSession = SalaryWorkSession::factory()->mixed()->create();

    expect($salaryWorkSession->salary_amount)->toBeGreaterThan(0)
        ->and($salaryWorkSession->salary_amount_cashless)->toBeGreaterThan(0);
});
