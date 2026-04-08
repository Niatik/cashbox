<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages;
use App\Models\Employee;
use App\Models\Rate;
use App\Models\SalaryRate;
use App\Models\WorkSession;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule
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
                    ->options(function (Get $get): Collection {
                        $employeeId = $get('employee_id');
                        if (! $employeeId) {
                            return collect();
                        }
                        $employee = Employee::find($employeeId);
                        if (! $employee) {
                            return collect();
                        }

                        return SalaryRate::query()
                            ->where('job_title_id', $employee->job_title_id)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->live(),
                Forms\Components\Select::make('rate_id')
                    ->label('Ставка')
                    ->options(function (Get $get): Collection {
                        $employeeId = $get('employee_id');
                        if (! $employeeId) {
                            return collect();
                        }
                        $employee = Employee::find($employeeId);
                        if (! $employee) {
                            return collect();
                        }

                        return Rate::query()
                            ->where('job_title_id', $employee->job_title_id)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'salaryRate',
                'rate',
                'salaryWorkSessions',
                'expenseWorkSessions',
            ]))
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
                Tables\Columns\TextColumn::make('salary_total')
                    ->label('К выплате')
                    ->state(fn (WorkSession $record): float => $record->salary_total)
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('salary_sum')
                    ->label('Выплачено')
                    ->state(fn (WorkSession $record): int => $record->salaryWorkSessions->sum(
                        fn ($s) => $s->salary_amount + $s->salary_amount_cashless
                    ))
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
                            ->label('Дата'),
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
