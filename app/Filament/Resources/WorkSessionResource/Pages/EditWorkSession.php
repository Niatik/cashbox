<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use App\Models\Payment;
use App\Models\RateRatio;
use App\Models\SalaryRate;
use App\Models\SalaryWorkSession;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Log;

class EditWorkSession extends EditRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->salaryWorkSessions()->exists()) {
            return [
                Actions\Action::make('back')
                    ->label('Вернуться к списку')
                    ->url($this->getResource()::getUrl('index'))
                    ->color('gray'),
            ];
        }

        return parent::getFormActions();
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                ...collect(parent::form($form)->getComponents(withHidden: true))
                    ->map(function (Forms\Components\Component $component) {
                        $component->disabled(fn (): bool => $this->record->salaryWorkSessions()->exists());

                        if ($component instanceof Forms\Components\Select && in_array($component->getName(), ['salary_rate_id', 'rate_id'])) {
                            $component->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                $this->recalculateSalaryFields($get, $set);
                            });
                        }

                        if ($component instanceof Forms\Components\Select && $component->getName() === 'employee_id') {
                            $component->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                $set('rate_id', null);
                                $set('salary_rate_id', null);
                                $this->recalculateSalaryFields($get, $set);
                            });
                        }

                        return $component;
                    })
                    ->all(),                Forms\Components\Section::make('Расходы смены')
                    ->schema([
                        Forms\Components\Repeater::make('expenseWorkSessions')
                            ->label('')
                            ->relationship()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                if ($this->record->salaryWorkSessions()->count() === 0) {
                                    $items = $get('expenseWorkSessions') ?? [];
                                    $total = collect($items)->sum(fn ($item) => (float) ($item['amount'] ?? 0));
                                    $set('salary_work_session.expense_total', $total);
                                    $salaryTotal = (float) ($get('salary_work_session.balance_salary') ?? 0) + (float) ($get('salary_work_session.income_total') ?? 0) - $total;
                                    $set('salary_work_session.salary_total', $salaryTotal);
                                    $set('salary_work_session.salary_amount', $salaryTotal);
                                    $set('salary_work_session.salary_remainder', 0);
                                }
                            })
                            ->schema([
                                Forms\Components\TextInput::make('expense_type')
                                    ->label('Тип расхода')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Сумма')
                                    ->required()
                                    ->numeric()
                                    ->live(),
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
                                            ->whereHas('workSession', fn ($q) => $q
                                                ->where('employee_id', $this->record->employee_id)
                                                ->where('date', '<', $this->record->date))
                                            ->get()
                                            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount);

                                        $component->state($balance);
                                    })
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('income_total')
                                    ->label('Доход')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                        $set('salary_total', (float) ($get('balance_salary') ?? 0) + (float) ($get('income_total') ?? 0) - (float) ($get('expense_total') ?? 0));
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component): void {
                                        Log::debug('afterStateHydrated');
                                        Log::debug($this->record);
                                        $session = $this->record;
                                        // $sessionStart = $session->date->format('Y-m-d').$session->time;
                                        $sessionStart = $session->time;
                                        Log::debug($session->date);
                                        Log::debug($sessionStart);

                                        $paymentSum = Payment::query()
                                            ->where('payment_date', $session->date)
                                            ->where('payment_time', '>=', $sessionStart)
                                            ->sum(\DB::raw('payment_cash_amount + payment_cashless_amount'));
                                        Log::debug($session->date);
                                        Log::debug($sessionStart);
                                        Log::debug($paymentSum);

                                        $salary = $session->salaryRate?->salary ?? 0;

                                        Log::debug($salary);

                                        $ratioBonus = 0;
                                        if ($session->rate_id) {
                                            Log::debug($session->rate_id);
                                            $matchingRatio = RateRatio::query()
                                                ->where('rate_id', $session->rate_id)
                                                ->where('ratio_to', '>=', $paymentSum / 100)
                                                ->where('ratio_from', '<=', $paymentSum / 100)
                                                ->first();
                                            Log::debug($matchingRatio);

                                            if ($matchingRatio) {
                                                $ratioBonus = $matchingRatio->ratio;
                                            }
                                        }
                                        Log::debug($ratioBonus);

                                        $component->state($salary + $ratioBonus);
                                    }),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Расход')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                        $set('salary_total', (float) ($get('balance_salary') ?? 0) + (float) ($get('income_total') ?? 0) - (float) ($get('expense_total') ?? 0));
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component): void {
                                        $items = $this->record->expenseWorkSessions ?? collect();
                                        $total = $items->sum(fn ($item) => (float) ($item->amount ?? 0));
                                        $component->state($total);
                                    }),
                                Forms\Components\TextInput::make('salary_total')
                                    ->label('Зарплата')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->live(debounce: 1000)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $balance = (float) ($get('balance_salary') ?? 0);
                                        $income = (float) ($get('income_total') ?? 0);
                                        $expense = (float) ($get('expense_total') ?? 0);
                                        $component->state($balance + $income - $expense);
                                    })
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                        $total = (float) ($get('salary_total') ?? 0);
                                        $amount = (float) ($get('salary_amount') ?? 0);
                                        $set('salary_remainder', $total - $amount);
                                    }),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->numeric()
                                    ->default(0)
                                    ->live(debounce: 1000)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $balance = (float) ($get('balance_salary') ?? 0);
                                        $income = (float) ($get('income_total') ?? 0);
                                        $expense = (float) ($get('expense_total') ?? 0);
                                        $component->state($balance + $income - $expense);
                                    })
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                        $total = (float) ($get('salary_total') ?? 0);
                                        $amount = (float) ($get('salary_amount') ?? 0);
                                        $set('salary_remainder', $total - $amount);
                                    }),
                                Forms\Components\TextInput::make('salary_remainder')
                                    ->label('Остаток')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $total = (float) ($get('salary_total') ?? 0);
                                        $amount = (float) ($get('salary_amount') ?? 0);
                                        $component->state($total - $amount);
                                    }),
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
                                Forms\Components\TextInput::make('balance_salary')
                                    ->label('Баланс')
                                    ->numeric()
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component): void {
                                        $balance = SalaryWorkSession::query()
                                            ->whereHas('workSession', fn ($q) => $q
                                                ->where('employee_id', $this->record->employee_id)
                                                ->where('date', '<', $this->record->date))
                                            ->get()
                                            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount);

                                        $component->state($balance);
                                    })
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('income_total')
                                    ->label('Доход')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Расход')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('salary_total')
                                    ->label('Зарплата')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('salary_remainder')
                                    ->label('Остаток')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $total = (float) ($get('salary_total') ?? 0);
                                        $amount = (float) ($get('salary_amount') ?? 0);
                                        $component->state($total - $amount);
                                    }),
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

    private function calculateIncome(?string $salaryRateId, ?string $rateId): float
    {
        $session = $this->record;
        $sessionStart = $session->time;

        $paymentSum = Payment::query()
            ->where('payment_date', $session->date)
            ->where('payment_time', '>=', $sessionStart)
            ->sum(\DB::raw('payment_cash_amount + payment_cashless_amount'));

        $salary = 0;
        if ($salaryRateId) {
            $salaryRate = SalaryRate::find($salaryRateId);
            $salary = $salaryRate?->salary ?? 0;
        }

        $ratioBonus = 0;
        if ($rateId) {
            $matchingRatio = RateRatio::query()
                ->where('rate_id', $rateId)
                ->where('ratio_to', '>=', $paymentSum / 100)
                ->where('ratio_from', '<=', $paymentSum / 100)
                ->first();

            if ($matchingRatio) {
                $ratioBonus = $matchingRatio->ratio;
            }
        }

        return $salary + $ratioBonus;
    }

    private function recalculateSalaryFields(Forms\Get $get, Forms\Set $set): void
    {
        $this->recalculateBalance($get, $set);

        $income = $this->calculateIncome($get('salary_rate_id'), $get('rate_id'));
        $set('salary_work_session.income_total', $income);

        $balance = (float) ($get('salary_work_session.balance_salary') ?? 0);
        $expense = (float) ($get('salary_work_session.expense_total') ?? 0);
        $salaryTotal = $balance + $income - $expense;

        $set('salary_work_session.salary_total', $salaryTotal);
        $set('salary_work_session.salary_amount', $salaryTotal);
        $set('salary_work_session.salary_remainder', 0);
    }

    private function recalculateBalance(Forms\Get $get, Forms\Set $set): void
    {
        $employeeId = $get('employee_id');

        $balance = SalaryWorkSession::query()
            ->whereHas('workSession', fn ($q) => $q
                ->where('employee_id', $employeeId)
                ->where('date', '<', $this->record->date))
            ->get()
            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount);

        $set('salary_work_session.balance_salary', $balance);
    }
}
