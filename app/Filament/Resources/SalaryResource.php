<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryResource\Pages;
use App\Models\Salary;
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

class SalaryResource extends Resource
{
    protected static ?string $model = Salary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('resources.salary.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.salary.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label(__('fields.worker'))
                    ->relationship('employee', 'name', fn (Builder $query) => $query->where('is_hidden', false))
                    ->preload()
                    ->required(),
                DatePicker::make('salary_date')
                    ->label(__('fields.date'))
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                TextInput::make('description')
                    ->label(__('fields.expense_description')),

                TextInput::make('salary_amount')
                    ->label(__('fields.amount'))
                    ->required()
                    ->numeric(),
                Toggle::make('is_cash')
                    ->label(__('fields.is_cash'))
                    ->required()
                    ->default(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('salary_date')
                    ->label(__('columns.date'))
                    ->date('d.m.Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('columns.worker'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('columns.description')),
                Tables\Columns\TextColumn::make('salary_amount')
                    ->label(__('columns.salary_amount'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_cash')
                    ->label(__('columns.is_cash')),
            ])
            ->defaultSort('salary_date', 'desc')
            ->filters(
                self::getTableFilters()
            )
            ->actions([
                Tables\Actions\EditAction::make()->label(__('messages.edit'))->hiddenLabel(true),
                Tables\Actions\DeleteAction::make()->label(__('messages.delete'))->hiddenLabel(true),
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
            'index' => Pages\ListSalaries::route('/'),
            'create' => Pages\CreateSalary::route('/create'),
            'edit' => Pages\EditSalary::route('/{record}/edit'),
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
                            fn (Builder $query, $date): Builder => $query->whereDate('salary_date', '=', $date),
                        );
                }),
        ];
    }
}
