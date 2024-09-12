<?php

use App\Filament\Resources\ExpenseResource;
use App\Models\User;
use App\Models\Expense;
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
    $this->get(ExpenseResource::getUrl('index'))->assertSuccessful();
});


it('can list of expenses', function () {
    $expenseTypes = Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->assertCanSeeTableRecords($expenseTypes);
});


it('can render page for creating an Expense', function () {
    $this->get(ExpenseResource::getUrl('create'))->assertSuccessful();
});


it('can create an Expense', function () {
    $newData = Expense::factory()->make();

    livewire(ExpenseResource\Pages\CreateExpense::class)
        ->fillForm([
            'expense_date' => $newData->expense_date,
            'expense_type_id' => $newData->expense_type_id,
            'expense_amount' => $newData->expense_amount,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Expense::class, [
        'expense_date' => $newData->expense_date,
        'expense_type_id' => $newData->expense_type_id,
        'expense_amount' => $newData->expense_amount * 100,
    ]);
});


it('can validate input to create an Expense', function () {
    livewire(ExpenseResource\Pages\CreateExpense::class)
        ->fillForm([
            'expense_date' => null,
            'expense_type_id' => null,
            'expense_amount' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'expense_date' => 'required',
            'expense_type_id' => 'required',
            'expense_amount' => 'required',
        ]);
});


it('can render page for editing the Expense', function () {
    $this->get(ExpenseResource::getUrl('edit', [
        'record' => Expense::factory()->create(),
    ]))->assertSuccessful();
});


it('can retrieve data for editing the Expense', function () {
    $expense = Expense::factory()->create();

    livewire(ExpenseResource\Pages\EditExpense::class, [
        'record' => $expense->getRouteKey(),
    ])
        ->assertFormFieldExists('expense_date')
        ->assertFormFieldExists('expense_type_id')
        ->assertFormFieldExists('expense_amount')
        ->assertFormSet([
            'expense_date' => $expense->expense_date,
            'expense_type_id' => $expense->expense_type_id,
            'expense_amount' => $expense->expense_amount,
        ]);
});


it('can save edited Expense', function () {
    $expense = Expense::factory()->create();
    $newData = Expense::factory()->make();

    livewire(ExpenseResource\Pages\EditExpense::class, [
        'record' => $expense->getRouteKey(),
    ])
        ->fillForm([
            'expense_date' => $newData->expense_date,
            'expense_type_id' => $newData->expense_type_id,
            'expense_amount' => $newData->expense_amount,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($expense->refresh())
        ->expense_date->toBe($newData->expense_date)
        ->expense_type_id->toBe($newData->expense_type_id)
        ->expense_amount->toBe($newData->expense_amount);
});


it('can validate input to edit the Expense', function () {
    $expense = Expense::factory()->create();

    livewire(ExpenseResource\Pages\EditExpense::class, [
        'record' => $expense->getRouteKey(),
    ])
        ->fillForm([
            'expense_date' => null,
            'expense_type_id' => null,
            'expense_amount' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'expense_date' => 'required',
            'expense_type_id' => 'required',
            'expense_amount' => 'required',
        ]);
});


it('can delete the Expense', function () {
    $expense = Expense::factory()->create();

    livewire(ExpenseResource\Pages\EditExpense::class, [
        'record' => $expense->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($expense);
});


it('can render the expense columns', function () {
    Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->assertCanRenderTableColumn('expense_date')
        ->assertCanRenderTableColumn('expense_type.name')
        ->assertCanRenderTableColumn('expense_amount');
});


it('can search types of expenses by date', function () {
    $expenses = Expense::factory()->count(10)->create();

    $date = $expenses->first()->expense_date;

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->searchTable($date)
        ->assertCanSeeTableRecords($expenses->where('expense_date', $date))
        ->assertCanNotSeeTableRecords($expenses->where('expense_date', '!=', $date));
});


it('can search types of expenses by type', function () {
    $expenses = Expense::factory()->count(10)->create();

    $type = $expenses->first()->expense_type->name;

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->searchTable($type)
        ->assertCanSeeTableRecords($expenses->where('expense_type.name', $type))
        ->assertCanNotSeeTableRecords($expenses->where('expense_type.name', '!=', $type));
});



it('can bulk delete the expenses from table', function () {
    $expenses = Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->callTableBulkAction(DeleteBulkAction::class, $expenses);

    foreach ($expenses as $expense) {
        $this->assertModelMissing($expense);
    }
});


it('can delete the expenses from table', function () {
    $expense = Expense::factory()->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->callTableAction(TableDeleteAction::class, $expense);

    $this->assertModelMissing($expense);
});


it('can edit the expenses from table', function () {
    $expense = Expense::factory()->create();
    $newData = Expense::factory()->make();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->callTableAction(EditAction::class, $expense, data: [
            'expense_date' => $newData->expense_date,
            'expense_type_id' => $newData->expense_type_id,
            'expense_amount' => $newData->expense_amount,
        ])
        ->assertHasNoTableActionErrors();

    expect($expense->refresh())
        ->expense_date->toBe($newData->expense_date)
        ->expense_type_id->toBe($newData->expense_type_id)
        ->expense_amount->toBe($newData->expense_amount);
});


it('can sort expenses by date', function () {
    $expenses = Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->sortTable('expense_date')
        ->assertCanSeeTableRecords($expenses->sortBy('expense_date'), inOrder: true)
        ->sortTable('expense_date', 'desc')
        ->assertCanSeeTableRecords($expenses->sortByDesc('expense_date'), inOrder: true);
});


it('can sort expenses by amount', function () {
    $expenses = Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->sortTable('expense_amount')
        ->assertCanSeeTableRecords($expenses->sortBy('expense_amount'), inOrder: true)
        ->sortTable('expense_amount', 'desc')
        ->assertCanSeeTableRecords($expenses->sortByDesc('expense_amount'), inOrder: true);
});


it('can sort expenses by expense type', function () {
    $expenses = Expense::factory()->count(10)->create();

    livewire(ExpenseResource\Pages\ListExpenses::class)
        ->sortTable('expense_type.name')
        ->assertCanSeeTableRecords($expenses->sortBy('expense_type.name'), inOrder: true)
        ->sortTable('expense_type.name', 'desc')
        ->assertCanSeeTableRecords($expenses->sortByDesc('expense_type.name'), inOrder: true);
});
