<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Имя пользователя')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->visible(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Подтверждение пароля')
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->required(fn (string $context): bool => $context === 'create')
                    ->visible(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->preload()
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->columnSpan('full'),
                Forms\Components\Fieldset::make('Информация о пользователе')
                    ->key('employee-fieldset')
                    ->relationship('employee')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Ф.И.О.')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                        Forms\Components\TextInput::make('salary')
                            ->label('Оклад')
                            ->numeric(),
                        Forms\Components\DatePicker::make('employment_date')
                            ->label('Дата приема'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя пользователя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Ф.И.О.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роль')
                    ->searchable()
                    ->sortable(),
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
            //RolesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
