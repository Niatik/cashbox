<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
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
                static::getDateFormField()->hidden(),
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
                            ->defaultItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['payment_date'] = now()->format('Y-m-d');

                                return $data;
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
            ->readOnly();
    }

    public static function getTimeFormField(): TimePicker
    {
        return TimePicker::make('order_time')
            ->timezone('Etc/GMT-5')
            ->default(now())
            ->label('Время')
            ->required()
            ->readOnly();
    }

    public static function getPriceFormField(): Select
    {
        return Select::make('price_id')
            ->relationship('price', 'name')
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
                ->orderBy('name_item')
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
            ->live(debounce: 1000)
            ->required()
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                [$price, $priceFactor, $discount, $prepayment, $additionalDiscount] = self::setVariablesForSumCalculation($get);
                $sum = $price * $priceFactor * $state - $prepayment - $discount - $additionalDiscount;
                $netSum = $price * $priceFactor * $state;
                $set('sum', $sum);
                $set('net_sum', $netSum);
                // TODO: Ввести сумму оплаты в первый элемент Repeater
                $set('payments.0.payment_cashless_amount', $sum);

            })
            ->hidden(fn (Get $get): bool => $get('name_item') == 'Количество человек');
    }

    public static function getSocialMediaFormField(): Select
    {
        return Select::make('social_media_id')
            ->relationship('social_media', 'name')
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
            ->relationship('customer', 'name')
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
            ->default(now())
            ->label('Дата')
            ->required();
    }

    public static function getPaymentCashAmountFormField(): TextInput
    {
        return TextInput::make('payment_cash_amount')
            // TODO: Исправить правило с учетом Repeater
            /*->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $numValue = intval(floatval($value) * 100) / 100;
                    $cashlessAmount = intval(floatval($get('payment_cashless_amount')) * 100) / 100;
                    $sum = $get('../../sum');
                    if (($cashlessAmount + $numValue) !== $sum) {
                        $fail('The total amount of payments does not match the order amount');
                    }
                },
            ])*/
            ->default('')
            ->numeric()
            ->live(debounce: 1000)
            ->label('Наличные')
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                $sum = $get('../../sum');
                $cashAmount = intval(floatval($state) * 100) / 100;
                if ($sum - $cashAmount) {
                    $set('payment_cashless_amount', $sum - $cashAmount);
                } else {
                    $set('payment_cashless_amount', '');
                }
            });

    }

    public static function getPaymentCashlessAmountFormField(): TextInput
    {
        return TextInput::make('payment_cashless_amount')
            // TODO: Исправить правило с учетом Repeater
            /*->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $numValue = intval(floatval($value) * 100) / 100;
                    $cashAmount = intval(floatval($get('payment_cash_amount')) * 100) / 100;
                    $sum = $get('../../sum');
                    if (($cashAmount + $numValue) !== $sum) {
                        $fail('The total amount of payments does not match the order amount');
                    }
                },
            ])*/
            ->default('')
            ->numeric()
            ->live(debounce: 1000)
            ->label('Безналичные')
            // TODO: Исправить обновление с учетом Repeater
            ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                $sum = $get('../../sum');
                $cashlessAmount = $state;
                if ($sum - $cashlessAmount) {
                    $set('payment_cash_amount', $sum - $cashlessAmount);
                } else {
                    $set('payment_cash_amount', '');
                }
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
                    ->date('H:i:s')
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
            ])
            ->filters(
                self::getTableFilters()
            )
            ->actions([
                Tables\Actions\EditAction::make()->label('Изменить')->hiddenLabel(),
                Tables\Actions\DeleteAction::make()->label('Удалить')->hiddenLabel(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
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

        $peopleNumber = $get('name_item') == 'Количество человек' ? 1 : $get('people_number');
        $sum = $price * $peopleNumber * $priceFactor - $discount - $prepayment - $additionalDiscount;
        $netSum = $price * $priceFactor * $peopleNumber;
        $set('sum', $sum);
        $set('net_sum', $netSum);
        $set('payments.0.payment_cashless_amount', $sum);
        $set('payments.0.payment_cash_amount', '');
    }

    public static function calcSumFromOptions(Get $get, Set $set): void
    {
        [$price, $priceFactor, $discount, $prepayment, $additionalDiscount, $isCash] = self::setVariablesForSumCalculationFromOptions($get);
        $peopleNumber = $get('../name_item') == 'Количество человек' ? 1 : $get('../people_number');
        $sum = $price * $priceFactor * $peopleNumber - $discount - $prepayment - $additionalDiscount;
        $netSum = $price * $priceFactor * $peopleNumber;
        $set('../sum', $sum);
        $set('../net_sum', $netSum);
        $set('../payments.0.payment_cashless_amount', $isCash ? '' : $sum);
        $set('../payments.0.payment_cash_amount', $isCash ? $sum : '');
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
                ->query(function (Builder $query, array $data, Get $get): Builder {
                    return $query
                        ->when(
                            $data['select_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('order_date', '=', $date),
                        );
                }),
        ];
    }
}
