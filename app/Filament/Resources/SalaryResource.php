<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryResource\Pages;
use App\Models\Salary;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryResource extends Resource
{
    protected static ?string $model = Salary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Зарплата';

    protected static ?string $pluralLabel = 'Зарплата';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Работник')
                    ->relationship('employee', 'name')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Ф.И.О.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                        TextInput::make('salary')
                            ->label('Оклад')
                            ->numeric(),
                        DatePicker::make('employment_date')
                            ->label('Дата приема на работу'),

                    ])
                    ->required(),
                DatePicker::make('salary_date')
                    ->label('Дата')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                TextInput::make('description')
                    ->label('Описание расхода'),

                TextInput::make('salary_amount')
                    ->label('Сумма')
                    ->required()
                    ->numeric(),
                Toggle::make('is_cash')
                    ->label('Наличные')
                    ->required()
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('salary_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Работник')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание'),
                Tables\Columns\TextColumn::make('salary_amount')
                    ->label('Сумма расхода')
                    ->sortable()
                    ->money('KZT'),
                Tables\Columns\ToggleColumn::make('is_cash')
                    ->label('Нал'),
            ])
            ->defaultSort('salary_date')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Изменить')->hiddenLabel(true),
                Tables\Actions\DeleteAction::make()->label('Удалить')->hiddenLabel(true),
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
}
