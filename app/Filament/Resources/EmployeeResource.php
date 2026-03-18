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

    public static function getModelLabel(): string
    {
        return __('resources.employee.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.employee.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.nav_groups.references');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('fields.full_name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('fields.phone'))
                    ->tel(),
                Forms\Components\Select::make('job_title_id')
                    ->label(__('fields.job_title'))
                    ->relationship('jobTitle', 'title')
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('employment_date')
                    ->label(__('fields.employment_date')),
                Forms\Components\Select::make('user_id')
                    ->label(__('fields.linked_user'))
                    ->relationship('user', 'name')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label(__('fields.username'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('fields.email'))
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label(__('fields.password'))
                            ->password()
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_hidden')
                    ->label(__('fields.hidden'))
                    ->default(false),
                Forms\Components\TextInput::make('fio')
                    ->label(__('fields.fio'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('info')
                    ->label(__('fields.info'))
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('columns.full_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('columns.linked_user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('columns.phone'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobTitle.title')
                    ->label(__('columns.job_title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_date')
                    ->label(__('columns.hire_date'))
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_hidden')
                    ->label(__('columns.hidden')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('messages.edit'))->hiddenLabel(true),
                Tables\Actions\DeleteAction::make()->label(__('messages.delete'))->hiddenLabel(true),
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
