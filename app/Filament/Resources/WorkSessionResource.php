<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages;
use App\Models\WorkSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getModelLabel(): string
    {
        return __('resources.work_session.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.work_session.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label(__('fields.employee'))
                    ->relationship('employee', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label(__('fields.date'))
                    ->default(now())
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TimePicker::make('time')
                    ->timezone('Etc/GMT-5')
                    ->displayFormat('H:i')
                    ->seconds(false)
                    ->default(now())
                    ->label(__('fields.time'))
                    ->required(),
                Forms\Components\Select::make('salary_rate_id')
                    ->label(__('fields.salary_rate'))
                    ->relationship('salaryRate', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('rate_id')
                    ->label(__('fields.rate'))
                    ->relationship('rate', 'name')
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('columns.employee'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('columns.date'))
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->date('H:i')
                    ->label(__('columns.time'))
                    ->timezone('Etc/GMT-5')
                    ->sortable(),
                Tables\Columns\TextColumn::make('salaryRate.name')
                    ->label(__('columns.salary_rate'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate.name')
                    ->label(__('columns.rate'))
                    ->sortable(),
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
            'index' => Pages\ListWorkSessions::route('/'),
            'create' => Pages\CreateWorkSession::route('/create'),
            'edit' => Pages\EditWorkSession::route('/{record}/edit'),
        ];
    }
}
