<?php

use App\Filament\Resources\ExpenseTypeResource;
use App\Models\User;
use App\Models\ExpenseType;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render page', function () {
    $this->get(ExpenseTypeResource::getUrl('index'))->assertSuccessful();
});

it('can list types of expenses', function () {
    $expenseTypes = ExpenseType::factory()->count(10)->create();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->assertCanSeeTableRecords($expenseTypes);
});

it('can render page for creating the Type of Expense', function () {
    $this->get(ExpenseTypeResource::getUrl('create'))->assertSuccessful();
});

it('can create a Type of Expense', function () {
    $newData = ExpenseType::factory()->make();

    livewire(ExpenseTypeResource\Pages\CreateExpenseType::class)
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ExpenseType::class, [
        'name' => $newData->name,
    ]);
});

it('can validate input to create the Type of Expense', function () {
    livewire(ExpenseTypeResource\Pages\CreateExpenseType::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can render page for editing the Type of Expense', function () {
    $this->get(ExpenseTypeResource::getUrl('edit', [
        'record' => ExpenseType::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data for editing the Type of Expense', function () {
    $expenseType = ExpenseType::factory()->create();

    livewire(ExpenseTypeResource\Pages\EditExpenseType::class, [
        'record' => $expenseType->getRouteKey(),
    ])
        ->assertFormFieldExists('name')
        ->assertFormSet([
            'name' => $expenseType->name,
        ]);
});

it('can save edited Type of Expense', function () {
    $expenseType = ExpenseType::factory()->create();
    $newData = ExpenseType::factory()->make();

    livewire(ExpenseTypeResource\Pages\EditExpenseType::class, [
        'record' => $expenseType->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($expenseType->refresh())
        ->name->toBe($newData->name);
});

it('can validate input to edit the Type of Expense', function () {
    $expenseType = ExpenseType::factory()->create();

    livewire(ExpenseTypeResource\Pages\EditExpenseType::class, [
        'record' => $expenseType->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can delete the Type of Expense', function () {
    $expenseType = ExpenseType::factory()->create();

    livewire(ExpenseTypeResource\Pages\EditExpenseType::class, [
        'record' => $expenseType->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($expenseType);
});

it('can render type of expense columns', function () {
    ExpenseType::factory()->count(10)->create();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->assertCanRenderTableColumn('name');
});

it('can search types of expenses by name', function () {
    $expenseTypes = ExpenseType::factory()->count(10)->create();

    $name = $expenseTypes->first()->name;

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->searchTable($name)
        ->assertCanSeeTableRecords($expenseTypes->where('name', $name))
        ->assertCanNotSeeTableRecords($expenseTypes->where('name', '!=', $name));
});

it('can sort types of expenses by name', function () {
    $expenseTypes = ExpenseType::factory()->count(10)->create();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($expenseTypes->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($expenseTypes->sortByDesc('name'), inOrder: true);
});

it('can bulk delete types of expenses from table', function () {
    $expenseTypes = ExpenseType::factory()->count(10)->create();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->callTableBulkAction(DeleteBulkAction::class, $expenseTypes);

    foreach ($expenseTypes as $expenseType) {
        $this->assertModelMissing($expenseType);
    }
});

it('can delete type of expenses from table', function () {
    $expenseType = ExpenseType::factory()->create();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->callTableAction(TableDeleteAction::class, $expenseType);

    $this->assertModelMissing($expenseType);
});

it('can edit types of expenses from table', function () {
    $expenseType = ExpenseType::factory()->create();
    $newData = ExpenseType::factory()->make();

    livewire(ExpenseTypeResource\Pages\ListExpenseTypes::class)
        ->callTableAction(EditAction::class, $expenseType, data: [
            'name' => $newData->name,
        ])
        ->assertHasNoTableActionErrors();

    expect($expenseType->refresh())
        ->name->toBe($newData->name);
});
