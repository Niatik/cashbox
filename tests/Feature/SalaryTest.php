<?php

use App\Filament\Resources\SalaryResource;
use App\Models\User;
use App\Models\Salary;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(SalaryResource::getUrl('index'))->assertSuccessful();
});

it('can list of salaries', function () {
    $expenseTypes = Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->assertCanSeeTableRecords($expenseTypes);
});

it('can render page for creating a Salary', function () {
    $this->get(SalaryResource::getUrl('create'))->assertSuccessful();
});

it('can create a Salary', function () {
    $newData = Salary::factory()->make();

    livewire(SalaryResource\Pages\CreateSalary::class)
        ->fillForm([
            'salary_date' => $newData->salary_date,
            'employee_id' => $newData->employee_id,
            'salary_amount' => $newData->salary_amount,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Salary::class, [
        'salary_date' => $newData->salary_date,
        'employee_id' => $newData->employee_id,
        'salary_amount' => $newData->salary_amount * 100,
    ]);
});

it('can validate input to create a Salary', function () {
    livewire(SalaryResource\Pages\CreateSalary::class)
        ->fillForm([
            'salary_date' => null,
            'employee_id' => null,
            'salary_amount' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'salary_date' => 'required',
            'employee_id' => 'required',
            'salary_amount' => 'required',
        ]);
});

it('can render page for editing the Salary', function () {
    $this->get(SalaryResource::getUrl('edit', [
        'record' => Salary::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Salary', function () {
    $salary = Salary::factory()->create();

    livewire(SalaryResource\Pages\EditSalary::class, [
        'record' => $salary->getRouteKey(),
    ])
        ->assertFormFieldExists('salary_date')
        ->assertFormFieldExists('employee_id')
        ->assertFormFieldExists('salary_amount')
        ->assertFormSet([
            'salary_date' => $salary->salary_date,
            'employee_id' => $salary->employee_id,
            'salary_amount' => $salary->salary_amount,
        ]);
});

it('can save edited Salary', function () {
    $salary = Salary::factory()->create();
    $newData = Salary::factory()->make();

    livewire(SalaryResource\Pages\EditSalary::class, [
        'record' => $salary->getRouteKey(),
    ])
        ->fillForm([
            'salary_date' => $newData->salary_date,
            'employee_id' => $newData->employee_id,
            'salary_amount' => $newData->salary_amount,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($salary->refresh())
        ->salary_date->toBe($newData->salary_date)
        ->employee_id->toBe($newData->employee_id)
        ->salary_amount->toBe($newData->salary_amount);
});

it('can validate input to edit the Salary', function () {
    $salary = Salary::factory()->create();

    livewire(SalaryResource\Pages\EditSalary::class, [
        'record' => $salary->getRouteKey(),
    ])
        ->fillForm([
            'salary_date' => null,
            'employee_id' => null,
            'salary_amount' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'salary_date' => 'required',
            'employee_id' => 'required',
            'salary_amount' => 'required',
        ]);
});

it('can delete the Salary', function () {
    $salary = Salary::factory()->create();

    livewire(SalaryResource\Pages\EditSalary::class, [
        'record' => $salary->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($salary);
});

it('can render the salary columns', function () {
    Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->assertCanRenderTableColumn('salary_date')
        ->assertCanRenderTableColumn('employee.name')
        ->assertCanRenderTableColumn('salary_amount');
});

it('can search salaries by date', function () {
    $salaries = Salary::factory()->count(10)->create();

    $date = $salaries->first()->salary_date;

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->searchTable($date)
        ->assertCanSeeTableRecords($salaries->where('salary_date', $date))
        ->assertCanNotSeeTableRecords($salaries->where('salary_date', '!=', $date));
});

it('can search salaries by employee', function () {
    $salaries = Salary::factory()->count(10)->create();

    $employee_name = $salaries->first()->employee->name;

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->searchTable($employee_name)
        ->assertCanSeeTableRecords($salaries->where('employee.name', $employee_name))
        ->assertCanNotSeeTableRecords($salaries->where('employee.name', '!=', $employee_name));
});

it('can sort salaries by date', function () {
    $salaries = Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->sortTable('salary_date')
        ->assertCanSeeTableRecords($salaries->sortBy('salary_date'), inOrder: true)
        ->sortTable('salary_date', 'desc')
        ->assertCanSeeTableRecords($salaries->sortByDesc('salary_date'), inOrder: true);
});


it('can sort salaries by amount', function () {
    $salaries = Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->sortTable('salary_amount')
        ->assertCanSeeTableRecords($salaries->sortBy('salary_amount'), inOrder: true)
        ->sortTable('salary_amount', 'desc')
        ->assertCanSeeTableRecords($salaries->sortByDesc('salary_amount'), inOrder: true);
});


it('can sort salaries by employee name', function () {
    $salaries = Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->sortTable('employee.name')
        ->assertCanSeeTableRecords($salaries->sortBy('employee.name'), inOrder: true)
        ->sortTable('employee.name', 'desc')
        ->assertCanSeeTableRecords($salaries->sortByDesc('employee.name'), inOrder: true);
});

it('can bulk delete the salaries from table', function () {
    $salaries = Salary::factory()->count(10)->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->callTableBulkAction(DeleteBulkAction::class, $salaries);

    foreach ($salaries as $salary) {
        $this->assertModelMissing($salary);
    }
});

it('can delete the salaries from table', function () {
    $salary = Salary::factory()->create();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->callTableAction(TableDeleteAction::class, $salary);

    $this->assertModelMissing($salary);
});

it('can edit the salaries from table', function () {
    $salary = Salary::factory()->create();
    $newData = Salary::factory()->make();

    livewire(SalaryResource\Pages\ListSalaries::class)
        ->callTableAction(EditAction::class, $salary, data: [
            'salary_date' => $newData->salary_date,
            'employee_id' => $newData->employee_id,
            'salary_amount' => $newData->salary_amount,
        ])
        ->assertHasNoTableActionErrors();

    expect($salary->refresh())
        ->salary_date->toBe($newData->salary_date)
        ->employee_id->toBe($newData->employee_id)
        ->salary_amount->toBe($newData->salary_amount);
});
