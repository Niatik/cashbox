<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages;
use App\Models\WorkSession;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Выплаты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Сотрудник')
                    ->relationship('employee', 'name')
                    ->preload()
                    ->required()
                    ->live()
                    ->unique(
                        table: 'work_sessions',
                        column: 'employee_id',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule
                            ->where('date', $get('date') ?? now()->toDateString())
                    )
                    ->validationMessages([
                        'unique' => 'Этот сотрудник уже имеет смену на эту дату.',
                    ])
                    ->afterStateUpdated(function (Forms\Set $set): void {
                        $set('rate_id', null);
                        $set('salary_rate_id', null);
                    }),
                Forms\Components\DatePicker::make('date')
                    ->label('Дата')
                    ->default(now())
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TimePicker::make('time')
                    ->timezone('Etc/GMT-5')
                    ->displayFormat('H:i')
                    ->seconds(false)
                    ->default(now())
                    ->label('Время')
                    ->required(),
                Forms\Components\Select::make('salary_rate_id')
                    ->label('Оклад')
                    ->relationship('salaryRate', 'name')
                    ->preload()
                    ->required()
                    ->live(),
                Forms\Components\Select::make('rate_id')
                    ->label('Ставка')
                    ->relationship('rate', 'name')
                    ->preload()
                    ->required()
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Сотрудник')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->date('H:i')
                    ->label('Время')
                    ->timezone('Etc/GMT-5')
                    ->sortable(),
                Tables\Columns\TextColumn::make('salaryWorkSessions.salary_amount')
                    ->label('Зарплата')
                    ->sortable(),
                Tables\Columns\TextColumn::make('salaryRate.name')
                    ->label('Ставка зарплаты')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate.name')
                    ->label('Тариф')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Дата')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('date', $date),
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['date']) {
                            return null;
                        }

                        return 'Дата: '.Carbon::parse($data['date'])->format('d.m.Y');
                    }),
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
            'index' => Pages\ListWorkSessions::route('/'),
            'create' => Pages\CreateWorkSession::route('/create'),
            'edit' => Pages\EditWorkSession::route('/{record}/edit'),
        ];
    }
}
