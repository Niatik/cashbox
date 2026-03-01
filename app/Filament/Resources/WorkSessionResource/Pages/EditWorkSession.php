<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use App\Models\Payment;
use App\Models\RateRatio;
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
                    ->map(fn (Forms\Components\Component $component) => $component
                        ->disabled(fn (): bool => $this->record->salaryWorkSessions()->exists()))
                    ->all(),
                Forms\Components\Section::make('Расходы смены')
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
                                    $set('salary_work_session.salary_total', (float) ($get('salary_work_session.balance_salary') ?? 0) + (float) ($get('salary_work_session.income_total') ?? 0) - $total);
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
                                            Log::debug($session);
                                            $matchingRatio = RateRatio::query()
                                                ->where('rate_id', $session->rate_id)
                                                ->whereRaw('CAST(ratio_to AS UNSIGNED) <= ?', [$paymentSum])
                                                ->whereRaw('CAST(ratio_from AS UNSIGNED) >= ?', [$paymentSum])
                                                ->first();

                                            if ($matchingRatio) {
                                                $ratioBonus = $matchingRatio->ratio;
                                            }
                                        }
                                        Log::debug($ratioBonus);

                                        $component->state($salary + $ratioBonus);
                                    }),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Общий расход')
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
                                    ->label('Итого зарплата')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $balance = (float) ($get('balance_salary') ?? 0);
                                        $income = (float) ($get('income_total') ?? 0);
                                        $expense = (float) ($get('expense_total') ?? 0);
                                        $component->state($balance + $income - $expense);
                                    }),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->numeric()
                                    ->default(0)
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get): void {
                                        $balance = (float) ($get('balance_salary') ?? 0);
                                        $income = (float) ($get('income_total') ?? 0);
                                        $expense = (float) ($get('expense_total') ?? 0);
                                        $component->state($balance + $income - $expense);
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
                                            ->whereHas('workSession', fn ($q) => $q->where('date', '<', $this->record->date))
                                            ->get()
                                            ->sum(fn (SalaryWorkSession $s): float => $s->income_total - $s->expense_total - $s->salary_amount);

                                        $component->state($balance);
                                    })
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('income_total')
                                    ->label('Общий доход')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('expense_total')
                                    ->label('Общий расход')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('salary_total')
                                    ->label('Итого зарплата')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('salary_amount')
                                    ->label('Сумма выплаты')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
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
