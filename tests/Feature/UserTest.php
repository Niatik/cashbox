<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can list users', function () {
    User::factory()->count(5)->create();
    $users = User::all();

    livewire(UserResource\Pages\ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can render page for creating the User', function () {
    $this->get(UserResource::getUrl('create'))->assertSuccessful();
});

it('can create an User', function () {
    $newData = User::factory()->make();
    $employee = Employee::factory()->make();

    livewire(UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'password' => $newData->password,
            'password_confirmation' => $newData->password,
            'employee.name' => $employee->name,
            'employee.phone' => $employee->phone,
            'employee.salary' => $employee->salary,
            'employee.employment_date' => $employee->employment_date,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name' => $newData->name,
        'email' => $newData->email,
        'password' => $newData->password,
    ]);

    $this->assertDatabaseHas(Employee::class, [
        'name' => $employee->name,
        'phone' => $employee->phone,
        'salary' => $employee->salary * 100,
        'employment_date' => $employee->employment_date,
    ]);
});

it('can validate input to create the User', function () {
    livewire(UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => null,
            'password' => null,
            'password_confirmation' => null,
            'employee.name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required',
            'employee.name' => 'required',
        ]);
});

it('can render page for editing the User ', function () {
    $this->get(UserResource::getUrl('edit', [
        'record' => User::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the User', function () {
    $user = User::factory()
        ->has(Employee::factory())
        ->create();

    livewire(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->assertFormFieldExists('employee.name')
        ->assertFormFieldExists('employee.phone')
        ->assertFormFieldExists('employee.salary')
        ->assertFormFieldExists('employee.employment_date')
        ->assertFormFieldDoesNotExist('password')
        ->assertFormFieldDoesNotExist('password_confirmation')
        ->assertFormSet([
            'name' => $user->name,
            'email' => $user->email,
            'employee.name' => $user->employee->name,
            'employee.phone' => $user->employee->phone,
            'employee.salary' => $user->employee->salary,
            'employee.employment_date' => $user->employee->employment_date,
        ]);
});

it('can save edited User', function () {
    $user = User::factory()
        ->has(Employee::factory())
        ->create();
    $newData = User::factory()->make();
    $newEmployee = Employee::factory()->make();

    livewire(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'employee.name' => $newEmployee->name,
            'employee.phone' => $newEmployee->phone,
            'employee.salary' => $newEmployee->salary,
            'employee.employment_date' => $newEmployee->employment_date,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->name->toBe($newData->name)
        ->email->toBe($newData->email)
        ->employee->name->toBe($newEmployee->name)
        ->employee->phone->toBe($newEmployee->phone)
        ->employee->salary->toBe($newEmployee->salary)
        ->employee->employment_date->toBe($newEmployee->employment_date);
});

it('can validate input to edit the User', function () {
    $user = User::factory()->create();

    livewire(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
            'email' => null,
            'employee.name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'required',
            'employee.name' => 'required',
        ]);
});

it('can delete the User', function () {
    $user = User::factory()->create();

    livewire(UserResource\Pages\EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($user);
});

it('can render users columns', function () {
    User::factory()->count(10)->create();

    livewire(UserResource\Pages\ListUsers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('employee.name')
        ->assertCanRenderTableColumn('employee.phone');
});

it('can search users by username', function () {
    $users = User::factory()->count(10)->create();

    $name = $users->first()->name;

    livewire(UserResource\Pages\ListUsers::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($users->where('name', $name))
        ->assertCanNotSeeTableRecords($users->where('name', '!=', $name));
});

it('can search users by employee name', function () {
    $users = User::factory()
        ->count(10)
        ->has(Employee::factory())
        ->create();

    $name = $users->first()->employee->name;

    livewire(UserResource\Pages\ListUsers::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($users->where('employee.name', $name))
        ->assertCanNotSeeTableRecords($users->where('employee.name', '!=', $name));
});

it('can search users by email', function () {
    $users = User::factory()->count(10)->create();

    $email = $users->first()->email;

    livewire(UserResource\Pages\ListUsers::class)
        ->searchTable($email)
        ->assertCanSeeTableRecords($users->where('email', $email))
        ->assertCanNotSeeTableRecords($users->where('email', '!=', $email));
});

it('can search users by employee phone', function () {
    User::factory()
        ->count(9)
        ->create();

    $users = User::All();

    foreach ($users as $user) {
        $user->employee->phone = fake()->phoneNumber();
        $user->save();
        $user->refresh();
    }

    $phone = $users->first()->employee->phone;

    livewire(UserResource\Pages\ListUsers::class)
        ->searchTable($phone)
        ->assertCanSeeTableRecords($users->where('employee.phone', $phone))
        ->assertCanNotSeeTableRecords($users->where('employee.phone', '!=', $phone));
});

it('can sort users by username', function () {
    User::factory()->count(5)->create();
    $users = User::All();

    livewire(UserResource\Pages\ListUsers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($users->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($users->sortByDesc('name'), inOrder: true);
});

it('can sort users by email', function () {
    User::factory()->count(5)->create();
    $users = User::All();

    livewire(UserResource\Pages\ListUsers::class)
        ->sortTable('email')
        ->assertCanSeeTableRecords($users->sortBy('email'), inOrder: true)
        ->sortTable('email', 'desc')
        ->assertCanSeeTableRecords($users->sortByDesc('email'), inOrder: true);
});

it('can sort users by employee name', function () {
    User::factory()
        ->count(4)
        ->create();
    $users = User::All();

    livewire(UserResource\Pages\ListUsers::class)
        ->sortTable('employee.name')
        ->assertCanSeeTableRecords($users->sortBy('employee.name'), inOrder: true)
        ->sortTable('employee.name', 'desc')
        ->assertCanSeeTableRecords($users->sortByDesc('employee.name'), inOrder: true);
});

it('can sort users by employee phone', function () {
    User::factory()
        ->count(4)
        ->create();
    $users = User::All();
    foreach ($users as $user) {
        $user->employee->phone = fake()->phoneNumber();
        $user->save();
        $user->refresh();
    }

    livewire(UserResource\Pages\ListUsers::class)
        ->sortTable('employee.phone')
        ->assertCanSeeTableRecords($users->sortBy('employee.phone'), inOrder: true)
        ->sortTable('employee.phone', 'desc')
        ->assertCanSeeTableRecords($users->sortByDesc('employee.phone'), inOrder: true);
});

it('can bulk delete users from table', function () {
    $users = User::factory()->count(10)->create();

    livewire(UserResource\Pages\ListUsers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $users);

    foreach ($users as $user) {
        $this->assertModelMissing($user);
    }
});

it('can delete users from table', function () {
    $user = User::factory()->create();

    livewire(UserResource\Pages\ListUsers::class)
        ->callTableAction(TableDeleteAction::class, $user);

    $this->assertModelMissing($user);
});

it('can edit users from table', function () {
    $user = User::factory()
        ->has(Employee::factory())
        ->create();
    $newData = User::factory()->make();
    $newEmployee = Employee::factory()->make();

    livewire(UserResource\Pages\ListUsers::class)
        ->callTableAction(EditAction::class, $user, data: [
            'name' => $newData->name,
            'email' => $newData->email,
            'employee' => [
                'name' => $newEmployee->name,
                'phone' => $newEmployee->phone,
                'salary' => $newEmployee->salary,
                'employment_date' => $newEmployee->employment_date,
            ],
        ])
        ->assertHasNoTableActionErrors();

    expect($user->refresh())
        ->name->toBe($newData->name)
        ->email->toBe($newData->email)
        ->employee->name->toBe($newEmployee->name)
        ->employee->phone->toBe($newEmployee->phone)
        ->employee->salary->toBe($newEmployee->salary)
        ->employee->employment_date->toBe($newEmployee->employment_date);
});

it('can render relation manager for Roles', function () {
    $user = User::factory()
        ->has(Role::factory()->count(3))
        ->create();

    livewire(UserResource\RelationManagers\RolesRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->assertSuccessful();
});

test('employee fieldset exists', function () {
    $user = User::factory()
        ->has(Employee::factory())
        ->create();
    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->assertFormComponentExists('employee-fieldset');
});
