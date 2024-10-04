<?php

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(RoleResource::getUrl('index'))->assertSuccessful();
});

it('can list of roles', function () {
    $roles = Role::all();

    livewire(RoleResource\Pages\ListRoles::class)
        ->assertCanSeeTableRecords($roles);
});

it('can render page for creating a Role', function () {
    $this->get(RoleResource::getUrl('create'))->assertSuccessful();
});

it('can create a Role', function () {
    $newData = Role::factory()->make();

    livewire(RoleResource\Pages\CreateRole::class)
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Role::class, [
        'name' => $newData->name,
    ]);
});

it('can validate input to create a Role', function () {
    livewire(RoleResource\Pages\CreateRole::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ]);
});

it('can render page for editing the Role', function () {
    $this->get(RoleResource::getUrl('edit', [
        'record' => Role::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Role', function () {
    $role = Role::factory()->create();

    livewire(RoleResource\Pages\EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->assertFormFieldExists('name')
        ->assertFormSet([
            'name' => $role->name,
        ]);
});

it('can save edited Role', function () {
    $role = Role::factory()->create();
    $newData = Role::factory()->make();

    livewire(RoleResource\Pages\EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->refresh())
        ->name->toBe($newData->name);
});

it('can validate input to edit the Role', function () {
    $role = Role::factory()->create();

    livewire(RoleResource\Pages\EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ]);
});

it('can delete the Role', function () {
    $role = Role::factory()->create();

    livewire(RoleResource\Pages\EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($role);
});

it('can render the role columns', function () {
    Role::factory()->count(10)->create();

    livewire(RoleResource\Pages\ListRoles::class)
        ->assertCanRenderTableColumn('name');
});

it('can search roles by name', function () {
    Role::factory()->create(
        ['name' => 'user'],
    );
    Role::factory()->create(
        ['name' => 'admin'],
    );
    $roles = Role::all();

    $name = $roles->first()->name;

    livewire(RoleResource\Pages\ListRoles::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($roles->where('name', $name))
        ->assertCanNotSeeTableRecords($roles->where('name', '!=', $name));
});

it('can sort roles by name', function () {
    Role::factory()->create(
        ['name' => 'user'],
    );
    Role::factory()->create(
        ['name' => 'admin'],
    );
    $roles = Role::all();

    livewire(RoleResource\Pages\ListRoles::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($roles->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($roles->sortByDesc('name'), inOrder: true);
});

it('can bulk delete the roles from table', function () {
    $roles = Role::factory()->count(10)->create();

    livewire(RoleResource\Pages\ListRoles::class)
        ->callTableBulkAction(DeleteBulkAction::class, $roles);

    foreach ($roles as $role) {
        $this->assertModelMissing($role);
    }
});

it('can delete the role from table', function () {
    $role = Role::factory()->create();

    livewire(RoleResource\Pages\ListRoles::class)
        ->callTableAction(TableDeleteAction::class, $role);

    $this->assertModelMissing($role);
});

it('can edit the roles from table', function () {
    $role = Role::factory()->create();
    $newData = Role::factory()->make();

    livewire(RoleResource\Pages\ListRoles::class)
        ->callTableAction(EditAction::class, $role, data: [
            'name' => $newData->name,
        ])
        ->assertHasNoTableActionErrors();

    expect($role->refresh())
        ->name->toBe($newData->name);
});

it('can render relation manager', function () {
    $role = Role::factory()
        ->has(Permission::factory()->count(10))
        ->create();

    livewire(RoleResource\RelationManagers\PermissionsRelationManager::class, [
        'ownerRecord' => $role,
        'pageClass' => EditRole::class,
    ])
        ->assertSuccessful();
});
