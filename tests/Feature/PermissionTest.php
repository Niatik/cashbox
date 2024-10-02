<?php

use App\Filament\Resources\PermissionResource;
use App\Models\User;
use App\Models\Permission;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(
        User::factory()->create()
    );
});


it('can render page', function () {
    $this->get(PermissionResource::getUrl('index'))->assertSuccessful();
});


it('can list of permissions', function () {
    $permissions = Permission::factory()->count(10)->create();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->assertCanSeeTableRecords($permissions);
});


it('can render page for creating a Permission', function () {
    $this->get(PermissionResource::getUrl('create'))->assertSuccessful();
});


it('can create a Permission', function () {
    $newData = Permission::factory()->make();

    livewire(PermissionResource\Pages\CreatePermission::class)
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Permission::class, [
        'name' => $newData->name,
    ]);
});


it('can validate input to create a Permission', function () {
    livewire(PermissionResource\Pages\CreatePermission::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ]);
});


it('can render page for editing the Permission', function () {
    $this->get(PermissionResource::getUrl('edit', [
        'record' => Permission::factory()->create(),
    ]))->assertSuccessful();
});


it('can retrieve data for editing the Permission', function () {
    $permission = Permission::factory()->create();

    livewire(PermissionResource\Pages\EditPermission::class, [
        'record' => $permission->getRouteKey(),
    ])
        ->assertFormFieldExists('name')
        ->assertFormSet([
            'name' => $permission->name,
        ]);
});


it('can save edited Permission', function () {
    $permission = Permission::factory()->create();
    $newData = Permission::factory()->make();

    livewire(PermissionResource\Pages\EditPermission::class, [
        'record' => $permission->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($permission->refresh())
        ->name->toBe($newData->name);
});


it('can validate input to edit the Permission', function () {
    $permission = Permission::factory()->create();

    livewire(PermissionResource\Pages\EditPermission::class, [
        'record' => $permission->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ]);
});


it('can delete the Permission', function () {
    $permission = Permission::factory()->create();

    livewire(PermissionResource\Pages\EditPermission::class, [
        'record' => $permission->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($permission);
});


it('can render the permission columns', function () {
    Permission::factory()->count(10)->create();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->assertCanRenderTableColumn('name');
});


it('can search permissions by name', function () {
    $permissions = Permission::factory()->count(10)->create();

    $name = $permissions->first()->name;

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($permissions->where('name', $name))
        ->assertCanNotSeeTableRecords($permissions->where('name', '!=', $name));
});


it('can sort permissions by name', function () {
    $permissions = Permission::factory()->count(10)->create();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($permissions->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($permissions->sortByDesc('name'), inOrder: true);
});


it('can bulk delete the permissions from table', function () {
    $permissions = Permission::factory()->count(10)->create();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->callTableBulkAction(DeleteBulkAction::class, $permissions);

    foreach ($permissions as $permission) {
        $this->assertModelMissing($permission);
    }
});


it('can delete the permissions from table', function () {
    $permission = Permission::factory()->create();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->callTableAction(TableDeleteAction::class, $permission);

    $this->assertModelMissing($permission);
});


it('can edit the permissions from table', function () {
    $permission = Permission::factory()->create();
    $newData = Permission::factory()->make();

    livewire(PermissionResource\Pages\ListPermissions::class)
        ->callTableAction(EditAction::class, $permission, data: [
            'name' => $newData->name,
        ])
        ->assertHasNoTableActionErrors();

    expect($permission->refresh())
        ->name->toBe($newData->name);
});
