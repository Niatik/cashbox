<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Сотрудники';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ф.И.О.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel(),
                Forms\Components\Select::make('job_title_id')
                    ->label('Должность')
                    ->relationship('jobTitle', 'title')
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('employment_date')
                    ->label('Дата приема на работу'),
                Forms\Components\Select::make('user_id')
                    ->label('Пользователь')
                    ->relationship('user', 'name')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя пользователя')
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_hidden')
                    ->label('Скрытый')
                    ->default(false),
                Forms\Components\TextInput::make('fio')
                    ->label('ФИО')
                    ->maxLength(255),
                Forms\Components\Textarea::make('info')
                    ->label('Информация')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ф.И.О.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobTitle.title')
                    ->label('Должность')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_date')
                    ->label('Дата приема')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_hidden')
                    ->label('Скрытый'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Изменить')->hiddenLabel(true),
                Tables\Actions\DeleteAction::make()->label('Удалить')->hiddenLabel(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
