<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Price;
use App\Models\PriceItem;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Услуги';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getDateFormField(),
                static::getTimeFormField(),
                static::getPriceFormField(),
                static::getPriceItemFormField(),
                static::getPriceValueFormField(),
                static::getPriceFactorFormField(),
                static::getPeopleNumberFormField(),
                static::getPeopleItemFormField(),
                static::getNameOfPriceItemFormField(),
                static::getSocialMediaFormField(),
                static::getNetSumFormField(),
                static::getSumFormField(),
                static::getCustomerFormField(),
                static::getEmployeeFormField(),
                static::getOptionsFormField(),
                Section::make('Оплаты')
                    ->schema([
                        Repeater::make('payments')
                            ->label('Список оплат')
                            ->addActionLabel('Добавить оплату')
                            ->relationship()
                            ->schema([
                                OrderResource::getPaymentDateFormField()->hidden(),
                                OrderResource::getPaymentCashAmountFormField(),
                                OrderResource::getPaymentCashlessAmountFormField(),
                            ])
                            ->columns(3)
                            ->rules([
                                'required',
                                'array',
                                'min:1',
                                /*fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (! is_array($value)) {
                                        return;
                                    }

                                    $netSum = (float) ($get('net_sum') ?? 0);
                                    $totalPayments = 0;

                                    foreach ($value as $payment) {
                                        $cash = (float) ($payment['payment_cash_amount'] ?? 0);
                                        $cashless = (float) ($payment['payment_cashless_amount'] ?? 0);
                                        $totalPayments += $cash + $cashless;
                                    }

                                    if (round($totalPayments, 2) !== round($netSum, 2)) {
                                        $fail('Сумма всех платежей ('.number_format($totalPayments, 2).') должна быть равна сумме заказа ('.number_format($netSum, 2).')');
                                    }
                                },*/
                            ])
                            ->validationMessages([
                                'required' => 'Необходимо добавить хотя бы один платеж',
                                'min' => 'Необходимо добавить хотя бы один платеж',
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire): array {
                                // Use the order's date instead of today's date
                                $orderDate = $livewire->data['order_date'] ?? now()->format('Y-m-d');
                                $data['payment_date'] = $orderDate;

                                return $data;
                            })
                            ->defaultItems(function ($livewire) {
                                // For create operation: 1 default payment
                                // For edit operation: 0 payments (preserve existing)
                                return $livewire instanceof CreateOrder ? 1 : 0;
                            }),
                    ]),
            ]);
    }

    public static function getDateFormField(): DatePicker
    {
        return DatePicker::make('order_date')
            ->default(now())
            ->label('Дата')
            ->required()
            ->hidden(fn () => ! auth()->user()->hasRole(['admin', 'super-admin']))
            ->readOnly(fn () => ! auth()->user()->hasRole(['admin', 'super-admin']));
    }

    public static function getTimeFormField(): TimePicker
    {
        return TimePicker::make('order_time')
            ->timezone('Etc/GMT-5')
            ->format('H:i')
            ->default(now())
            ->label('Время')
            ->required()
            ->readOnly();
    }

    public static function getPriceFormField(): Select
    {
        return Select::make('price_id')
            ->relationship('price', 'name', fn (Builder $query) => $query->where('is_hidden', false))
            ->label('Услуга')
            ->searchable()
            ->preload()
            ->live(debounce: 1000)
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Название услуги')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('description')
                    ->label('Описание')
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Цена на одного человека')
                    ->maxLength(18)
                    ->required(),
            ])
            ->afterStateHydrated(function (?int $state, Set $set) {
                self::getPrice($state, $set);
            })
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                self::getPrice($state, $set);
                self::calcSum(null, $get, $set);
            })
            ->required();
    }

    public static function getPriceItemFormField(): Select
    {
        return Select::make('price_item_id')
            ->required()
            ->label(function (Select $component, Set $set): string {
                $currentOption = $component->getOptionLabel() ?? 'Время услуги';
                $peopleItem = 1;

                if (str_contains($currentOption, 'человек')) {
                    $peopleItem = intval(last(explode('/', $currentOption)));
                    $currentOption = 'Количество человек';
                }
                $set('name_item', $currentOption);
                $set('people_item', $peopleItem);

                return $currentOption == 'Количество человек' ? 'Количество человек' : 'Время услуги';
            })
            ->options(fn (Get $get): Collection => PriceItem::query()
                ->where('price_id', $get('price_id'))
                ->whereNotNull('name_item')
                ->orderBy('id')
                ->pluck('name_item', 'id'))
            ->live(debounce: 1000)
            ->afterStateHydrated(function (?int $state, Get $get, Set $set) {
                self::getPriceItem($state, $set);
            })
            ->afterStateUpdated(function (Select $component, ?int $state, Get $get, Set $set) {
                self::getPriceItem($state, $set);
                self::calcSum($component, $get, $set);
            });
    }

    public static function getPriceValueFormField(): Hidden
    {
        return Hidden::make('price')
            ->default(0);
    }

    public static function getPriceFactorFormField(): Hidden
    {
        return Hidden::make('price_factor')
            ->default(0);
    }

    public static function getNameOfPriceItemFormField(): Hidden
    {
        return Hidden::make('name_item')
            ->default('');
    }

    public static function getPeopleItemFormField(): Hidden
    {
        return Hidden::make('people_item')
            ->default(1);
    }

    public static function getPeopleNumberFormField(): TextInput
    {
        return TextInput::make('people_number')
            ->numeric()
            ->label('Количество человек')
            ->minValue(1)
            ->default(1)
            ->live(onBlur: true) // Trigger on blur to prevent duplicate updates
            ->required()
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                // Only calculate if we have a valid state and price data
                if (! $state || ! $get('price') || ! $get('price_factor')) {
                    return;
                }

                [$price, $priceFactor, $discount, $prepayment, $additionalDiscount] = self::setVariablesForSumCalculation($get);
                $sum = $price * $priceFactor * $state - $prepayment - $discount - $additionalDiscount;
                $netSum = $price * $priceFactor * $state;
                $set('sum', $sum);
                $set('net_sum', $netSum);

                // Only update the first payment if this is a new order creation
                if (! $get('id')) { // No ID means new order
                    $set('payments.0.payment_cashless_amount', $sum);
                }

            })
            ->hidden(fn (Get $get): bool => $get('name_item') == 'Количество человек')
            ->dehydrated(); // Ensure the value is always saved, even when hidden
    }

    public static function getSocialMediaFormField(): Select
    {
        return Select::make('social_media_id')
            ->relationship('social_media', 'name', fn (Builder $query) => $query->where('is_hidden', false))
            ->label('Откуда')
            ->searchable()
            ->preload()
            ->required()
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Название')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function getCustomerFormField(): Select
    {
        return Select::make('customer_id')
            ->relationship('customer', 'name', fn ($query) => $query->whereNotNull('name'))
            ->label('Клиент')
            ->searchable()
            ->preload()
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Ф.И.О.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required(),
            ]);
    }

    public static function getSumFormField(): TextInput
    {
        return TextInput::make('sum')
            ->numeric()
            ->label('Сумма к оплате')
            ->default(0)
            ->readOnly();
    }

    public static function getNetSumFormField(): TextInput
    {
        return TextInput::make('net_sum')
            ->numeric()
            ->label('Сумма')
            ->default(0)
            ->readOnly();
    }

    public static function getEmployeeFormField(): TextInput
    {
        return TextInput::make('employee_id')
            ->hidden();
    }

    public static function getPaymentDateFormField(): DatePicker
    {
        return DatePicker::make('payment_date')
            ->default(fn (Get $get) => $get('../../order_date') ?? now())
            ->label('Дата')
            ->required();
    }

    public static function getPaymentCashAmountFormField(): TextInput
    {
        return TextInput::make('payment_cash_amount')
            ->default('')
            ->numeric()
            ->live(debounce: 500)
            ->label('Наличные')
            ->afterStateHydrated(function (TextInput $component, ?string $state) {
                $state = $state ?? '';
                if ($state == '0') {
                    $component->state('');
                }
            })
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                // Получаем сумму заказа
                $sum = floatval($get('../../net_sum') ?? 0);
                $cashValue = floatval($state ?? 0);

                // Получаем все оплаты и считаем сумму других строк
                $payments = $get('../../payments') ?? [];
                $currentCashlessValue = floatval($get('payment_cashless_amount') ?? 0);

                $remaining = self::getRemaining($payments, $cashValue, $currentCashlessValue, $sum);
                $set('payment_cashless_amount', $remaining > 0 ? $remaining : '');
            });
    }

    public static function getPaymentCashlessAmountFormField(): TextInput
    {
        return TextInput::make('payment_cashless_amount')
            ->default('')
            ->numeric()
            ->live(debounce: 500)
            ->label('Безналичные')
            ->afterStateHydrated(function (TextInput $component, ?string $state) {
                $state = $state ?? '';
                if ($state == '0') {
                    $component->state('');
                }
            })
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                // Получаем сумму заказа
                $sum = floatval($get('../../net_sum') ?? 0);
                $cashlessValue = floatval($state ?? 0);

                // Получаем все оплаты и считаем сумму других строк
                $payments = $get('../../payments') ?? [];
                $currentCashValue = floatval($get('payment_cash_amount') ?? 0);

                $remaining = self::getRemaining($payments, $cashlessValue, $currentCashValue, $sum);
                $set('payment_cash_amount', $remaining > 0 ? $remaining : '');
            });
    }

    public static function getOptionsFormField(): Section
    {
        return Section::make()
            ->statePath('options')
            ->schema([
                TextInput::make('discount')
                    ->label('Скидка')
                    ->numeric()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        self::calcSumFromOptions($get, $set);
                    }),
                TextInput::make('prepayment')
                    ->label('Аванс')
                    ->numeric()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        self::calcSumFromOptions($get, $set);
                    }),
                Toggle::make('is_cash')
                    ->label('Наличные')
                    ->default(false)
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        self::calcSumFromOptions($get, $set);
                    }),
                TextInput::make('additional_discount')
                    ->label('Дополнительная скидка')
                    ->numeric()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        self::calcSumFromOptions($get, $set);
                    }),
                TextInput::make('additional_discount_description')
                    ->label('Причина дополнительной скидки')
                    ->hidden(fn (Get $get): bool => ! $get('additional_discount'))
                    ->required(fn (Get $get): bool => filled($get('additional_discount'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\ToggleColumn::make('is_paid')
                    ->label('Оплачено')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state === true) {
                            // This code runs when the toggle changes from false to true
                            // You could redirect to the order details page
                            return redirect()->route('filament.admin.resources.orders.edit', ['record' => $record->id]);
                        }
                    }),
                Tables\Columns\TextColumn::make('order_date')
                    ->hidden()
                    ->date('d.m.Y')
                    ->label('Дата')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_time')
                    ->date('H:i')
                    ->label('Время')
                    ->timezone('Etc/GMT-5')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price.name')
                    ->label('Услуга')
                    ->limit(22)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клиент')
                    ->limit(27)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_item.name_item')
                    ->label('Время')
                    ->sortable(),
                Tables\Columns\TextColumn::make('people_number')
                    ->numeric()
                    ->label('Люди')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sum')
                    ->numeric()
                    ->label('Сумма')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prepayment')
                    ->numeric()
                    ->label('Аванс')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->options['prepayment'] ?? 0),
                Tables\Columns\TextColumn::make('payment_cash_sum')
                    ->numeric()
                    ->label('Наличные')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum('payments as payment_cash_sum', 'payment_cash_amount')
                            ->orderBy('payment_cash_sum', $direction);
                    })
                    ->getStateUsing(fn ($record) => $record->payments->sum('payment_cash_amount')),
                Tables\Columns\TextColumn::make('payment_cashless_sum')
                    ->numeric()
                    ->label('Безналичные')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum('payments as payment_cashless_sum', 'payment_cashless_amount')
                            ->orderBy('payment_cashless_sum', $direction);
                    })
                    ->getStateUsing(fn ($record) => $record->payments->sum('payment_cashless_amount')),
                Tables\Columns\TextColumn::make('remaining')
                    ->numeric()
                    ->label('Остаток')
                    ->getStateUsing(function ($record) {
                        $sum = $record->net_sum;
                        $discount = $record->options['discount'] ?? 0;
                        $additionalDiscount = $record->options['additional_discount'] ?? 0;
                        $totalPaid = $record->payments->sum('payment_cash_amount') + $record->payments->sum('payment_cashless_amount');

                        return max(0, $sum - $totalPaid - $discount - $additionalDiscount);
                    }),
            ])
            ->paginated(false)
            ->defaultSort('order_time', 'desc')
            ->filters(
                self::getTableFilters()
            )
            ->actions([
                Tables\Actions\DeleteAction::make()->label('Удалить')->hiddenLabel(),
            ])
            ->bulkActions([
            ])
            ->selectable(false)
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getPrice(?int $state, Set $set): void
    {
        if ($state) {
            $price = Price::find($state);
            if ($price) {
                $price_value = $price->price;
                $set('price', $price_value);
            }
        }
    }

    public static function setVariablesForSumCalculation(Get $get): array
    {
        $price = intval(floatval($get('price')) * 100) / 100;
        $priceFactor = intval(floatval($get('price_factor')) * 1000) / 1000;
        $discount = intval(floatval($get('options.discount')) * 100) / 100;
        $prepayment = intval(floatval($get('options.prepayment')) * 100) / 100;
        $additionalDiscount = intval(floatval($get('options.additional_discount')) * 100) / 100;

        return [$price, $priceFactor, $discount, $prepayment, $additionalDiscount];
    }

    public static function setVariablesForSumCalculationFromOptions(Get $get): array
    {
        $price = intval(floatval($get('../price')) * 100) / 100;
        $priceFactor = intval(floatval($get('../price_factor')) * 1000) / 1000;
        $discount = intval(floatval($get('discount')) * 100) / 100;
        $prepayment = intval(floatval($get('prepayment')) * 100) / 100;
        $additionalDiscount = intval(floatval($get('additional_discount')) * 100) / 100;
        $isCash = $get('is_cash');

        return [$price, $priceFactor, $discount, $prepayment, $additionalDiscount, $isCash];
    }

    public static function getPriceItem(?int $state, Set $set): void
    {
        if ($state) {
            $priceItem = PriceItem::find($state);
            if ($priceItem) {
                $priceFactor = $priceItem->factor;
                $set('price_factor', $priceFactor);
            }
        }
    }

    public static function calcSum(?Select $component, Get $get, Set $set): void
    {
        [$price, $priceFactor, $discount, $prepayment, $additionalDiscount] = self::setVariablesForSumCalculation($get);

        // Preserve the current people_number value
        $currentPeopleNumber = $get('people_number');

        if ($component) {
            $currentOption = $component->getOptionLabel();
            $peopleItem = 1;
            if (str_contains($currentOption, 'человек')) {
                $peopleItem = intval(last(explode('/', $currentOption)));
                $currentOption = 'Количество человек';
            }
            $set('name_item', $currentOption);
            $set('people_item', $peopleItem);
        }

        // Use preserved people_number or default logic
        $peopleNumber = $get('name_item') == 'Количество человек' ? 1 : ($currentPeopleNumber ?: 1);

        // Only calculate if we have valid price data
        if ($price && $priceFactor) {
            $sum = $price * $peopleNumber * $priceFactor - $discount - $prepayment - $additionalDiscount;
            $netSum = $price * $priceFactor * $peopleNumber;
            $set('sum', $sum);
            $set('net_sum', $netSum);
            // Only update first payment if this is a new order creation
            if (! $get('id')) { // No ID means new order
                $set('payments.0.payment_cashless_amount', $sum);
                $set('payments.0.payment_cash_amount', '');
            }
        }
    }

    public static function calcSumFromOptions(Get $get, Set $set): void
    {
        [$price, $priceFactor, $discount, $prepayment, $additionalDiscount, $isCash] = self::setVariablesForSumCalculationFromOptions($get);

        // Preserve the current people_number value
        $currentPeopleNumber = $get('../people_number');
        $peopleNumber = $get('../name_item') == 'Количество человек' ? 1 : ($currentPeopleNumber ?: 1);

        // Only calculate if we have valid price data
        if ($price && $priceFactor) {
            $sum = $price * $priceFactor * $peopleNumber - $discount - $prepayment - $additionalDiscount;
            $netSum = $price * $priceFactor * $peopleNumber;
            $set('../sum', $sum);
            $set('../net_sum', $netSum);

            // Only update first payment if this is a new order creation
            if (! $get('../id')) { // No ID means new order
                $set('../payments.0.payment_cashless_amount', $isCash ? '' : $sum);
                $set('../payments.0.payment_cash_amount', $isCash ? $sum : '');
            }
        }
    }

    protected static function getTableFilters(): array
    {
        return [
            Filter::make('selected_date')
                ->default()
                ->form([
                    DatePicker::make('select_date')
                        ->default(now())
                        ->label('Выберите дату'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['select_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('order_date', '=', $date),
                        );
                }),
        ];
    }

    public static function getRemaining(mixed $payments, float $cashValue, float $currentCashlessValue, float $sum): mixed
    {
        $totalOtherPayments = 0;
        foreach ($payments as $key => $payment) {
            $paymentCash = floatval($payment['payment_cash_amount'] ?? 0);
            $paymentCashless = floatval($payment['payment_cashless_amount'] ?? 0);
            $totalOtherPayments += $paymentCash + $paymentCashless;
        }
        // Вычитаем текущую строку из общей суммы
        $totalOtherPayments -= $cashValue + $currentCashlessValue;

        $remaining = max(0, $sum - $totalOtherPayments - $cashValue);

        return $remaining;
    }
}
