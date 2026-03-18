<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('resources.expense.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.expense.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('expense_type_id')
                    ->label(__('fields.expense_type'))
                    ->relationship('expense_type', 'name')
                    ->preload()
                    ->required(),
                DatePicker::make('expense_date')
                    ->label(__('fields.expense_date'))
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                TextInput::make('description')
                    ->label(__('fields.expense_description')),
                TextInput::make('expense_amount')
                    ->label(__('fields.expense_amount'))
                    ->required()
                    ->numeric(),
                Toggle::make('is_cash')
                    ->label(__('fields.is_cash'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label(__('columns.expense_date'))
                    ->date('d.m.Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('expense_type.name')
                    ->label(__('columns.expense_type'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('columns.description')),
                Tables\Columns\TextColumn::make('expense_amount')
                    ->label(__('columns.expense_amount'))
                    ->sortable()
                    ->numeric(0),
                Tables\Columns\ToggleColumn::make('is_cash')
                    ->label(__('columns.is_cash')),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters(
                self::getTableFilters()
            )
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->hiddenLabel(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Filter::make('selected_date')
                ->default()
                ->form([
                    DatePicker::make('select_date')
                        ->default(now())
                        ->label(__('messages.select_date')),
                ])
                ->query(function (Builder $query, array $data, Get $get): Builder {
                    return $query
                        ->when(
                            $data['select_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '=', $date),
                        );
                }),
        ];
    }
}
