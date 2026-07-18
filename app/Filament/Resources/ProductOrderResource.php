<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductOrderResource\Pages;
use App\Filament\Resources\ProductOrderResource\Pages\CreateProductOrder;
use App\Models\Product;
use App\Models\ProductOrder;
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

class ProductOrderResource extends Resource
{
    protected static ?string $model = ProductOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Товары';

    protected static ?string $navigationLabel = 'Товары';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getDateFormField(),
                static::getTimeFormField(),
                static::getProductFormField(),
                static::getPriceFormField(),
                static::getQuantityFormField(),
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
                                static::getPaymentDateFormField()->hidden(),
                                static::getPaymentTimeFormField()->hidden(),
                                static::getPaymentCashAmountFormField(),
                                static::getPaymentCashlessAmountFormField(),
                            ])
                            ->columns(3)
                            ->rules([
                                'required',
                                'array',
                                'min:1',
                            ])
                            ->validationMessages([
                                'required' => 'Необходимо добавить хотя бы один платеж',
                                'min' => 'Необходимо добавить хотя бы один платеж',
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire): array {
                                $data['payment_date'] = now()->timezone('Etc/GMT-5')->format('Y-m-d');

                                return $data;
                            })
                            ->defaultItems(function ($livewire) {
                                return $livewire instanceof CreateProductOrder ? 1 : 0;
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
            ->native(false)
            ->displayFormat('H:i')
            ->seconds(false)
            ->default(now())
            ->label('Время')
            ->required()
            ->readOnly();
    }

    public static function getProductFormField(): Select
    {
        return Select::make('product_id')
            ->relationship('product', 'name')
            ->label('Товар')
            ->searchable()
            ->preload()
            ->live(debounce: 1000)
            ->afterStateHydrated(function (?int $state, Set $set) {
                self::setProductPrice($state, $set);
            })
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                self::setProductPrice($state, $set);
                self::calcSum($get, $set);
            })
            ->required();
    }

    public static function getPriceFormField(): Hidden
    {
        return Hidden::make('price')
            ->default(0);
    }

    public static function getQuantityFormField(): TextInput
    {
        return TextInput::make('quantity')
            ->numeric()
            ->label('Количество')
            ->minValue(1)
            ->default(1)
            ->live(onBlur: true)
            ->required()
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                if (! $state || ! $get('price')) {
                    return;
                }

                self::calcSum($get, $set);

                if (! $get('id')) {
                    $sum = floatval($get('sum') ?? 0);
                    $isCash = $get('options.is_cash') ?? false;
                    $set('payments.0.payment_cashless_amount', $isCash ? '' : $sum);
                    $set('payments.0.payment_cash_amount', $isCash ? $sum : '');
                }
            });
    }

    public static function getNetSumFormField(): TextInput
    {
        return TextInput::make('net_sum')
            ->numeric()
            ->label('Сумма')
            ->default(0)
            ->readOnly()
            ->dehydrated(false);
    }

    public static function getSumFormField(): TextInput
    {
        return TextInput::make('sum')
            ->numeric()
            ->label('Сумма к оплате')
            ->default(0)
            ->readOnly();
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

    public static function getEmployeeFormField(): TextInput
    {
        return TextInput::make('employee_id')
            ->hidden();
    }

    public static function getPaymentDateFormField(): DatePicker
    {
        return DatePicker::make('payment_date')
            ->default(fn (Get $get) => now()->timezone('Etc/GMT-5')->format('Y-m-d'))
            ->label('Дата')
            ->required();
    }

    public static function getPaymentTimeFormField(): Hidden
    {
        return Hidden::make('payment_time')
            ->default(now()->timezone('Etc/GMT-5')->format('H:i:s'));
    }

    public static function getPaymentCashAmountFormField(): TextInput
    {
        return TextInput::make('payment_cash_amount')
            ->default('')
            ->numeric()
            ->live(debounce: 1000)
            ->label('Наличные')
            ->extraInputAttributes([
                'x-data' => '{
                showRemaining() {
                    const data = $wire.get("data");
                    const netSum = parseFloat(data.net_sum || 0);
                    const discount = parseFloat(data.options?.discount || 0);
                    const additionalDiscount = parseFloat(data.options?.additional_discount || 0);
                    const payments = Object.values(data.payments || {});

                    const currentKey = $el.closest("[wire\\\\:key]")?.getAttribute("wire:key");

                    const otherPaymentsTotal = payments.reduce((sum, payment, index) => {
                        const paymentKey = Object.keys(data.payments)[index];
                        if (paymentKey === currentKey) return sum;

                        const cash = parseFloat(payment.payment_cash_amount || 0);
                        const cashless = parseFloat(payment.payment_cashless_amount || 0);
                        return sum + cash + cashless;
                    }, 0);

                    const remaining = netSum - discount - additionalDiscount - otherPaymentsTotal;

                    if (!$el.value || $el.value === "0") {
                        $el.value = remaining > 0 ? remaining.toFixed(0) : "";
                        $el.dispatchEvent(new Event("input", { bubbles: true }));
                    }
                }
            }',
                'x-on:focus' => 'showRemaining()',
            ])->afterStateHydrated(function (TextInput $component, ?string $state) {
                $state = $state ?? '';
                if ($state == '0') {
                    $component->state('');
                }
            })
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                $sum = floatval($get('../../net_sum') ?? 0);
                $cashValue = floatval($state ?? 0);
                $payments = $get('../../payments') ?? [];
                $currentCashlessValue = floatval($get('payment_cashless_amount') ?? 0);

                $discount = intval(floatval($get('../../options.discount')) * 100) / 100;
                $additionalDiscount = intval(floatval($get('../../options.additional_discount')) * 100) / 100;

                $remaining = self::getRemaining($payments, $cashValue, $currentCashlessValue, $sum, $discount, $additionalDiscount);
                $set('payment_cashless_amount', $remaining > 0 ? $remaining : '');
            });
    }

    public static function getPaymentCashlessAmountFormField(): TextInput
    {
        return TextInput::make('payment_cashless_amount')
            ->default('')
            ->numeric()
            ->live(debounce: 1000)
            ->label('Безналичные')
            ->extraInputAttributes([
                'x-data' => '{
                showRemaining() {
                    const data = $wire.get("data");
                    const netSum = parseFloat(data.net_sum || 0);
                    const discount = parseFloat(data.options?.discount || 0);
                    const additionalDiscount = parseFloat(data.options?.additional_discount || 0);
                    const payments = Object.values(data.payments || {});

                    const currentKey = $el.closest("[wire\\\\:key]")?.getAttribute("wire:key");

                    const otherPaymentsTotal = payments.reduce((sum, payment, index) => {
                        const paymentKey = Object.keys(data.payments)[index];
                        if (paymentKey === currentKey) return sum;

                        const cash = parseFloat(payment.payment_cash_amount || 0);
                        const cashless = parseFloat(payment.payment_cashless_amount || 0);
                        return sum + cash + cashless;
                    }, 0);

                    const remaining = netSum - discount - additionalDiscount - otherPaymentsTotal;

                    if (!$el.value || $el.value === "0") {
                        $el.value = remaining > 0 ? remaining.toFixed(0) : "";
                        $el.dispatchEvent(new Event("input", { bubbles: true }));
                    }
                }
            }',
                'x-on:focus' => 'showRemaining()',
            ])
            ->afterStateHydrated(function (TextInput $component, ?string $state) {
                $state = $state ?? '';
                if ($state == '0') {
                    $component->state('');
                }
            })
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                $sum = floatval($get('../../net_sum') ?? 0);
                $cashlessValue = floatval($state ?? 0);
                $payments = $get('../../payments') ?? [];
                $currentCashValue = floatval($get('payment_cash_amount') ?? 0);

                $discount = intval(floatval($get('../../options.discount')) * 100) / 100;
                $additionalDiscount = intval(floatval($get('../../options.additional_discount')) * 100) / 100;

                $remaining = self::getRemaining($payments, $cashlessValue, $currentCashValue, $sum, $discount, $additionalDiscount);
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
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Товар')
                    ->limit(22)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клиент')
                    ->limit(27)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->label('Кол-во')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sum')
                    ->numeric()
                    ->label('Сумма')
                    ->sortable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductOrders::route('/'),
            'create' => CreateProductOrder::route('/create'),
            'edit' => Pages\EditProductOrder::route('/{record}/edit'),
        ];
    }

    public static function setProductPrice(?int $state, Set $set): void
    {
        if ($state) {
            $product = Product::find($state);
            if ($product) {
                $set('price', $product->price);
            }
        }
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public static function setVariablesForSumCalculation(Get $get): array
    {
        $price = intval(floatval($get('price')) * 100) / 100;
        $quantity = intval($get('quantity') ?? 1);
        $discount = intval(floatval($get('options.discount')) * 100) / 100;
        $additionalDiscount = intval(floatval($get('options.additional_discount')) * 100) / 100;

        return [$price, $quantity, $discount, $additionalDiscount];
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: float, 4: bool}
     */
    public static function setVariablesForSumCalculationFromOptions(Get $get): array
    {
        $price = intval(floatval($get('../price')) * 100) / 100;
        $quantity = intval($get('../quantity') ?? 1);
        $discount = intval(floatval($get('discount')) * 100) / 100;
        $additionalDiscount = intval(floatval($get('additional_discount')) * 100) / 100;
        $isCash = $get('is_cash');

        return [$price, $quantity, $discount, $additionalDiscount, $isCash];
    }

    public static function calcSum(Get $get, Set $set): void
    {
        [$price, $quantity, $discount, $additionalDiscount] = self::setVariablesForSumCalculation($get);

        if ($price && $quantity) {
            $netSum = $price * $quantity;
            $sum = $netSum - $discount - $additionalDiscount;
            $set('net_sum', $netSum);
            $set('sum', $sum);

            if (! $get('id')) {
                $isCash = $get('options.is_cash') ?? false;
                $set('payments.0.payment_cashless_amount', $isCash ? '' : $sum);
                $set('payments.0.payment_cash_amount', $isCash ? $sum : '');
            }
        }
    }

    public static function calcSumFromOptions(Get $get, Set $set): void
    {
        [$price, $quantity, $discount, $additionalDiscount, $isCash] = self::setVariablesForSumCalculationFromOptions($get);

        if ($price && $quantity) {
            $netSum = $price * $quantity;
            $sum = $netSum - $discount - $additionalDiscount;
            $set('../net_sum', $netSum);
            $set('../sum', $sum);

            if (! $get('../id')) {
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

    public static function getRemaining(mixed $payments, float $cashValue, float $currentCashlessValue, float $sum, float $discount, float $additionalDiscount): mixed
    {
        $totalOtherPayments = 0;
        foreach ($payments as $payment) {
            $paymentCash = floatval($payment['payment_cash_amount'] ?? 0);
            $paymentCashless = floatval($payment['payment_cashless_amount'] ?? 0);
            $totalOtherPayments += $paymentCash + $paymentCashless;
        }
        $totalOtherPayments -= $cashValue + $currentCashlessValue;

        return max(0, $sum - $totalOtherPayments - $cashValue - $discount - $additionalDiscount);
    }
}
