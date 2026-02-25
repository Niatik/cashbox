<?php

use App\Filament\Resources\WorkSessionResource;
use App\Models\SalaryWorkSession;
use App\Models\WorkSession;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(WorkSessionResource::getUrl('index'))->assertSuccessful();
});

it('can list work sessions', function () {
    WorkSession::factory()->count(3)->create();

    $workSessions = WorkSession::all();

    livewire(WorkSessionResource\Pages\ListWorkSessions::class)
        ->assertCanSeeTableRecords($workSessions);
});

it('can render page for creating the WorkSession', function () {
    $this->get(WorkSessionResource::getUrl('create'))->assertSuccessful();
});

it('can create a WorkSession', function () {
    $newData = WorkSession::factory()->make();

    livewire(WorkSessionResource\Pages\CreateWorkSession::class)
        ->fillForm([
            'employee_id' => $newData->employee_id,
            'time' => $newData->time,
            'salary_rate_id' => $newData->salary_rate_id,
            'rate_id' => $newData->rate_id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(WorkSession::class, [
        'employee_id' => $newData->employee_id,
        'salary_rate_id' => $newData->salary_rate_id,
        'rate_id' => $newData->rate_id,
    ]);
});

it('can validate input to create the WorkSession', function () {
    livewire(WorkSessionResource\Pages\CreateWorkSession::class)
        ->fillForm([
            'employee_id' => null,
            'time' => null,
            'salary_rate_id' => null,
            'rate_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'employee_id' => 'required',
            'time' => 'required',
            'salary_rate_id' => 'required',
            'rate_id' => 'required',
        ]);
});

it('can render page for editing the WorkSession', function () {
    $this->get(WorkSessionResource::getUrl('edit', [
        'record' => WorkSession::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the WorkSession', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('employee_id')
        ->assertFormFieldExists('time')
        ->assertFormFieldExists('salary_rate_id')
        ->assertFormFieldExists('rate_id')
        ->assertFormSet([
            'employee_id' => $workSession->employee_id,
            'salary_rate_id' => $workSession->salary_rate_id,
            'rate_id' => $workSession->rate_id,
        ]);
});

it('can save edited WorkSession', function () {
    $workSession = WorkSession::factory()->create();
    $newData = WorkSession::factory()->make();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'employee_id' => $newData->employee_id,
            'time' => $newData->time,
            'salary_rate_id' => $newData->salary_rate_id,
            'rate_id' => $newData->rate_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $workSession->refresh();

    expect($workSession)
        ->employee_id->toBe($newData->employee_id)
        ->salary_rate_id->toBe($newData->salary_rate_id)
        ->rate_id->toBe($newData->rate_id);
});

it('can delete a WorkSession', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->callAction(Filament\Actions\DeleteAction::class);

    $this->assertModelMissing($workSession);
});

it('can delete a WorkSession from table', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\ListWorkSessions::class)
        ->callTableAction(TableDeleteAction::class, $workSession);

    $this->assertModelMissing($workSession);
});

it('can bulk delete WorkSessions', function () {
    $workSessions = WorkSession::factory()->count(3)->create();

    livewire(WorkSessionResource\Pages\ListWorkSessions::class)
        ->callTableBulkAction(DeleteBulkAction::class, $workSessions);

    foreach ($workSessions as $workSession) {
        $this->assertModelMissing($workSession);
    }
});

it('can render edit page with salary section when no SalaryWorkSession exists', function () {
    $workSession = WorkSession::factory()->create();

    $this->get(WorkSessionResource::getUrl('edit', [
        'record' => $workSession,
    ]))->assertSuccessful();

    expect($workSession->salaryWorkSessions()->count())->toBe(0);
});

it('shows salary form fields when no SalaryWorkSession exists', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('salary_work_session.income_total')
        ->assertFormFieldExists('salary_work_session.expense_total')
        ->assertFormFieldExists('salary_work_session.salary_total')
        ->assertFormFieldExists('salary_work_session.salary_amount')
        ->assertFormFieldExists('salary_work_session.is_cash');
});

it('creates SalaryWorkSession with form data when salary_payment action is called', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'salary_work_session.income_total' => 100,
            'salary_work_session.expense_total' => 50,
            'salary_work_session.salary_total' => 50,
            'salary_work_session.salary_amount' => 50,
            'salary_work_session.is_cash' => true,
        ])
        ->mountFormComponentAction('zarplata-smeny', 'salary_payment')
        ->callMountedFormComponentAction();

    $this->assertDatabaseHas(SalaryWorkSession::class, [
        'work_session_id' => $workSession->id,
    ]);

    $salary = SalaryWorkSession::where('work_session_id', $workSession->id)->first();
    expect($salary->income_total)->toBe(100.0)
        ->and($salary->expense_total)->toBe(50.0)
        ->and($salary->salary_total)->toBe(50.0)
        ->and($salary->salary_amount)->toBe(50.0)
        ->and($salary->is_cash)->toBeTrue();
});

it('does not persist SalaryWorkSession to database before payment action', function () {
    $workSession = WorkSession::factory()->create();

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->fillForm([
            'salary_work_session.income_total' => 100,
            'salary_work_session.salary_amount' => 100,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SalaryWorkSession::where('work_session_id', $workSession->id)->count())->toBe(0);
});

it('displays SalaryWorkSession data in form when one exists', function () {
    $workSession = WorkSession::factory()->create();
    SalaryWorkSession::factory()->create(['work_session_id' => $workSession->id]);

    livewire(WorkSessionResource\Pages\EditWorkSession::class, [
        'record' => $workSession->getRouteKey(),
    ])
        ->assertFormFieldExists('salaryWorkSessions');
});
