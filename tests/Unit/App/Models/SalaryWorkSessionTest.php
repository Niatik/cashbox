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
        ->and($salaryWorkSession->is_cash)->toBeBool();
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
