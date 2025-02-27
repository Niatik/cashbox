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

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Расходы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('expense_type_id')
                    ->label('Тип расхода')
                    ->relationship('expense_type', 'name')
                    ->preload()
                    ->required(),
                DatePicker::make('expense_date')
                    ->label('Дата раcхода')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                TextInput::make('description')
                    ->label('Описание расхода'),
                TextInput::make('expense_amount')
                    ->label('Сумма расхода')
                    ->required()
                    ->numeric(),
                Toggle::make('is_cash')
                    ->label('Наличные')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Дата раcхода')
                    ->date('d.m.Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('expense_type.name')
                    ->label('Тип расхода')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание'),
                Tables\Columns\TextColumn::make('expense_amount')
                    ->label('Сумма расхода')
                    ->sortable()
                    ->numeric(0),
                Tables\Columns\ToggleColumn::make('is_cash')
                    ->label('Нал'),
            ])
            ->defaultSort('expense_date')
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
                        ->label('Выберите дату'),
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
