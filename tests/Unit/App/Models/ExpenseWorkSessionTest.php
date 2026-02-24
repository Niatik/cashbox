<?php

use App\Models\ExpenseType;
use App\Models\ExpenseWorkSession;
use App\Models\WorkSession;

it('can create an expense work session', function () {
    $expenseWorkSession = ExpenseWorkSession::factory()->create();

    expect($expenseWorkSession)->toBeInstanceOf(ExpenseWorkSession::class)
        ->and($expenseWorkSession->work_session_id)->not->toBeNull()
        ->and($expenseWorkSession->expense_type)->not->toBeNull()
        ->and($expenseWorkSession->amount)->toBeFloat();
});

it('belongs to a work session', function () {
    $workSession = WorkSession::factory()->create();
    $expenseWorkSession = ExpenseWorkSession::factory()->create([
        'work_session_id' => $workSession->id,
    ]);

    expect($expenseWorkSession->workSession)
        ->toBeInstanceOf(WorkSession::class)
        ->and($expenseWorkSession->workSession->id)->toBe($workSession->id);
});

it('deletes expense work sessions when work session is deleted', function () {
    $workSession = WorkSession::factory()->create();
    ExpenseWorkSession::factory()->create(['work_session_id' => $workSession->id]);

    $workSession->delete();

    expect(ExpenseWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});
