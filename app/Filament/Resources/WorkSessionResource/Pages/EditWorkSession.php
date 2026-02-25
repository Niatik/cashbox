<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use App\Models\SalaryWorkSession;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditWorkSession extends EditRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                ...parent::form($form)->getComponents(withHidden: true),
                Forms\Components\Section::make('Расходы смены')
                    ->schema([
                        Forms\Components\Repeater::make('expenseWorkSessions')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('expense_type')
                                    ->label('Тип расхода')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Сумма')
                                    ->required()
                                    ->numeric(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Зарплата смены')
                    ->footerActionsAlignment(Alignment::Right)
                    ->footerActions([
                        Action::make('salary_payment')
                            ->label('Выплата')
                            ->icon('heroicon-o-banknotes')
                            ->requiresConfirmation()
                            ->action(function (Forms\Get $get) {
                                $data = $get('salary_work_session');

                                SalaryWorkSession::create([
                                    'work_session_id' => $this->record->id,
                                    'income_total' => $data['income_total'] ?? 0,
                                    'expense_total' => $data['expense_total'] ?? 0,
                                    'salary_total' => $data['salary_total'] ?? 0,
                                    'salary_amount' => $data['salary_amount'] ?? 0,
                                    'is_cash' => $data['is_cash'] ?? true,
                                ]);

                                $this->fillForm();
                            })
                            ->visible(fn (): bool => $this->record->salaryWorkSessions()->count() === 0),
                    ])
                    ->schema([
                        Forms\Components\Group::make()
                            ->statePath('salary_work_session')
                            ->schema([
                                Forms\Components\TextInput::make('balance_salary')
                                    ->label('Баланс')
                                    ->numeric()
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component): void {
                                        $balance = SalaryWorkSession::query()
                                            ->whereHas('workSession', fn ($q) => $q->where('date', '<', $this->record->date))
                                            ->get()
                                            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount);

                                        $component->state($balance);
                                    })
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('income_total')
                                    ->label('Общий доход')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Общий расход')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('salary_total')
                                    ->label('Итого зарплата')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_cash')
                                    ->label('Наличные')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn (): bool => $this->record->salaryWorkSessions()->count() === 0),
                        Forms\Components\Repeater::make('salaryWorkSessions')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('income_total')
                                    ->label('Общий доход')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Общий расход')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('salary_total')
                                    ->label('Итого зарплата')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\Toggle::make('is_cash')
                                    ->label('Наличные')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->visible(fn (): bool => $this->record->salaryWorkSessions()->count() > 0),
                    ]),
            ]);
    }
}
