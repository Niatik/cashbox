<?php

use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

beforeEach(function () {

    $this->seed([
        PermissionSeeder::class,
        RoleSeeder::class,
    ]);

    $this->actingAs(
        User::factory()->create()
            ->assignRole('super-admin')
    );
});


it('can render page', function () {
    $this->get(EmployeeResource::getUrl('index'))->assertSuccessful();
});


it('can list employees', function () {
    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->assertCanSeeTableRecords($employees);
});


it('can render page for creating the Employee', function () {
    $this->get(EmployeeResource::getUrl('create'))->assertSuccessful();
});


it('can create a Employee', function () {
    $newData = Employee::factory()->make();

    livewire(EmployeeResource\Pages\CreateEmployee::class)
        ->fillForm([
            'name' => $newData->name,
            'phone' => $newData->phone,
            'salary' => $newData->salary,
            'employment_date' => $newData->employment_date,
            'user_id' => $newData->user_id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Employee::class, [
        'name' => $newData->name,
        'phone' => $newData->phone,
        'salary' => $newData->salary * 100,
        'employment_date' => $newData->employment_date,
        'user_id' => $newData->user_id,
    ]);
});


it('can validate input to create the Employee', function () {
    livewire(EmployeeResource\Pages\CreateEmployee::class)
        ->fillForm([
            'name' => null,
            'phone' => null,
            'salary' => null,
            'employment_date' => null,
            'user_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'user_id' => 'required',
        ]);
});

it('can render page for editing the Employee ', function () {
    $this->get(EmployeeResource::getUrl('edit', [
        'record' => Employee::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Employee', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('phone')
        ->assertFormFieldExists('salary')
        ->assertFormFieldExists('employment_date')
        ->assertFormFieldExists('user_id')
        ->assertFormSet([
            'name' => $employee->name,
            'phone' => $employee->phone,
            'salary' => $employee->salary,
            'employment_date' => $employee->employment_date,
            'user_id' => $employee->user_id,
        ]);
});

it('can save edited Employee', function () {
    $employee = Employee::factory()->create();
    $newData = Employee::factory()->make();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'phone' => $newData->phone,
            'salary' => $newData->salary,
            'employment_date' => $newData->employment_date,
            'user_id' => $newData->user_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($employee->refresh())
        ->name->toBe($newData->name)
        ->phone->toBe($newData->phone)
        ->salary->toBe($newData->salary)
        ->employment_date->toBe($newData->employment_date)
        ->user_id->toBe($newData->user_id);
});


it('can validate input to edit the Employee', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
            'phone' => null,
            'salary' => null,
            'employment_date' => null,
            'user_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'user_id' => 'required',
        ]);
});


it('can delete the Employee', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\EditEmployee::class, [
        'record' => $employee->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($employee);
});


it('can render employee columns', function () {
    Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('user.name')
        ->assertCanRenderTableColumn('phone')
        ->assertCanRenderTableColumn('salary')
        ->assertCanRenderTableColumn('employment_date');
});


it('can search employees by name', function () {
    $employees = Employee::factory()->count(10)->create();

    $name = $employees->first()->name;

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($employees->where('name', $name))
        ->assertCanNotSeeTableRecords($employees->where('name', '!=', $name));
});


it('can search employees by phone', function () {
    $employees = Employee::factory()->count(10)->create();

    $phone = $employees->first()->phone;

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->searchTable($phone)
        ->assertCanSeeTableRecords($employees->where('phone', $phone))
        ->assertCanNotSeeTableRecords($employees->where('phone', '!=', $phone));
});

it('can search employees by username', function () {
    $employees = Employee::factory()->count(10)->create();

    $username = $employees->first()->user->name;

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->searchTable($username)
        ->assertCanSeeTableRecords($employees->where('user.name', $username))
        ->assertCanNotSeeTableRecords($employees->where('user.name', '!=', $username));
});


it('can bulk delete employees from table', function () {
    $employees = Employee::factory()->count(10)->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->callTableBulkAction(DeleteBulkAction::class, $employees);

    foreach ($employees as $employee) {
        $this->assertModelMissing($employee);
    }
});


it('can delete employees from table', function () {
    $employee = Employee::factory()->create();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->callTableAction(TableDeleteAction::class, $employee);

    $this->assertModelMissing($employee);
});


it('can edit employees from table', function () {
    $employee = Employee::factory()->create();
    $newData = Employee::factory()->make();

    livewire(EmployeeResource\Pages\ListEmployees::class)
        ->callTableAction(EditAction::class, $employee, data: [
            'name' => $newData->name,
            'phone' => $newData->phone,
            'salary' => $newData->salary,
            'employment_date' => $newData->employment_date,
            'user_id' => $newData->user_id,
        ])
        ->assertHasNoTableActionErrors();

    expect($employee->refresh())
        ->name->toBe($newData->name)
        ->phone->toBe($newData->phone)
        ->salary->toBe($newData->salary)
        ->employment_date->toBe($newData->employment_date)
        ->user_id->toBe($newData->user_id);
});
