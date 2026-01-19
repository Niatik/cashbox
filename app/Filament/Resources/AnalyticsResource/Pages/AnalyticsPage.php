<?php

namespace App\Filament\Resources\AnalyticsResource\Pages;

use App\Filament\Resources\AnalyticsResource;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Price;
use App\Models\PriceItem;
use App\Models\Salary;
use App\Models\SocialMedia;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;

class AnalyticsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AnalyticsResource::class;

    protected static string $view = 'filament.resources.analytics-resource.pages.analytics-page';

    protected static ?string $title = 'Панель аналитики';

    public ?array $data = [];

    public function getBreadcrumb(): string
    {
        return 'Аналитика';
    }

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth(),
            'date_to' => now()->endOfMonth(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Фильтры')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('С даты')
                            ->default(now()->startOfMonth())
                            ->live()
                            ->afterStateUpdated(fn () => $this->updateData()),

                        DatePicker::make('date_to')
                            ->label('По дату')
                            ->default(now()->endOfMonth())
                            ->live()
                            ->afterStateUpdated(fn () => $this->updateData()),

                        Select::make('price_id')
                            ->label('Услуга')
                            ->options(Price::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Все услуги')
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('price_item_id', null);
                                $this->updateData();
                            }),

                        Select::make('price_item_id')
                            ->label('Тип времени')
                            ->options(function (Get $get) {
                                $priceId = $get('price_id');
                                if (! $priceId) {
                                    return [];
                                }

                                return PriceItem::where('price_id', $priceId)
                                    ->pluck('name_item', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Все типы')
                            ->live()
                            ->visible(function (Get $get) {
                                $priceId = $get('price_id');
                                if (! $priceId) {
                                    return false;
                                }

                                $firstPriceItem = PriceItem::where('price_id', $priceId)
                                    ->orderBy('id')
                                    ->first();

                                if (! $firstPriceItem) {
                                    return false;
                                }

                                $name = mb_strtolower($firstPriceItem->name_item);

                                return str_contains($name, 'мин') || str_contains($name, 'час');
                            })
                            ->afterStateUpdated(fn () => $this->updateData()),
                    ])
                    ->columns(4)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function updateData(): void
    {
        // Trigger data refresh when dates change
        $this->dispatch('data-updated');
    }

    // Get financial summary data for first table
    public function getFinancialSummary(): array
    {
        $dateFrom = $this->data['date_from'] ?? now()->startOfMonth();
        $dateTo = $this->data['date_to'] ?? now()->endOfMonth();
        $priceId = $this->data['price_id'] ?? null;
        $priceItemId = $this->data['price_item_id'] ?? null;

        // Get orders query with optional price filter
        $ordersQuery = Order::query();
        if ($priceId) {
            $ordersQuery->where('price_id', $priceId);
        }
        if ($priceItemId) {
            $ordersQuery->where('price_item_id', $priceItemId);
        }
        $orderIds = $ordersQuery->pluck('id');

        // Cash income from payments (filter by payment_date and price)
        $cashIncome = Payment::whereIn('order_id', $orderIds)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('payment_cash_amount') / 100;

        // Cashless income from payments (filter by payment_date and price)
        $cashlessIncome = Payment::whereIn('order_id', $orderIds)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('payment_cashless_amount') / 100;

        // Total expenses (including salaries) - convert from integer to decimal
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('expense_amount') / 100;

        $salaryExpenses = Salary::whereBetween('salary_date', [$dateFrom, $dateTo])
            ->sum('salary_amount') / 100;

        $totalExpenses = $expenses + $salaryExpenses;

        // Total income is cash + cashless
        $totalIncome = $cashIncome + $cashlessIncome;

        // Profit calculation
        $profit = $totalIncome - $totalExpenses;

        return [
            'income' => $totalIncome,
            'profit' => $profit,
            'expenses' => $totalExpenses,
            'cash_income' => $cashIncome,
            'cashless_income' => $cashlessIncome,
        ];
    }

    // Get social media analytics for second table
    public function getSocialMediaAnalytics(): array
    {
        $dateFrom = $this->data['date_from'] ?? now()->startOfMonth();
        $dateTo = $this->data['date_to'] ?? now()->endOfMonth();
        $priceId = $this->data['price_id'] ?? null;
        $priceItemId = $this->data['price_item_id'] ?? null;

        return SocialMedia::all()
            ->map(function ($socialMedia) use ($dateFrom, $dateTo, $priceId, $priceItemId) {
                // Get orders for this social media with optional price filter
                $ordersQuery = Order::where('social_media_id', $socialMedia->id);
                if ($priceId) {
                    $ordersQuery->where('price_id', $priceId);
                }
                if ($priceItemId) {
                    $ordersQuery->where('price_item_id', $priceItemId);
                }
                $orderIds = $ordersQuery->pluck('id');

                // Get actual payments for these orders, filtered by payment_date
                $payments = Payment::whereIn('order_id', $orderIds)
                    ->whereBetween('payment_date', [$dateFrom, $dateTo])
                    ->get();

                // Get orders that had payments in the date range for people count
                $ordersWithPayments = Order::whereIn('id', $payments->pluck('order_id')->unique())
                    ->where('people_number', '<', 1000)
                    ->get();

                $ordersCount = $ordersWithPayments->count(); // Count unique orders, not payments
                $totalPeople = $ordersWithPayments->sum('people_number');
                $totalSum = $payments->sum('payment_cash_amount') + $payments->sum('payment_cashless_amount');

                return [
                    'name' => $socialMedia->name,
                    'orders_count' => $ordersCount,
                    'total_people' => $totalPeople,
                    'total_sum' => $totalSum,
                ];
            })
            ->filter(fn ($item) => $item['orders_count'] > 0)
            ->sortByDesc('total_sum')
            ->values()
            ->toArray();
    }

    // Get expense analytics for third table
    public function getExpenseAnalytics(): array
    {
        $dateFrom = $this->data['date_from'] ?? now()->startOfMonth();
        $dateTo = $this->data['date_to'] ?? now()->endOfMonth();

        // Regular expenses grouped by type (convert from integer to decimal)
        $expenses = ExpenseType::all()
            ->map(function ($expenseType) use ($dateFrom, $dateTo) {
                $totalAmount = Expense::where('expense_type_id', $expenseType->id)
                    ->whereBetween('expense_date', [$dateFrom, $dateTo])
                    ->sum('expense_amount') / 100; // Convert from integer to decimal

                return [
                    'type' => $expenseType->name,
                    'total_amount' => $totalAmount,
                ];
            })
            ->filter(fn ($item) => $item['total_amount'] > 0)
            ->toArray();

        // Add salary expenses (convert from integer to decimal)
        $salaryTotal = Salary::whereBetween('salary_date', [$dateFrom, $dateTo])
            ->sum('salary_amount') / 100;

        if ($salaryTotal > 0) {
            $expenses[] = [
                'type' => 'Зарплата',
                'total_amount' => $salaryTotal,
            ];
        }

        // Sort by amount descending
        usort($expenses, fn ($a, $b) => $b['total_amount'] <=> $a['total_amount']);

        return $expenses;
    }

    protected function getViewData(): array
    {
        return [
            'financialSummary' => $this->getFinancialSummary(),
            'socialMediaAnalytics' => $this->getSocialMediaAnalytics(),
            'expenseAnalytics' => $this->getExpenseAnalytics(),
        ];
    }
}
