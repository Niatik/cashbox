<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Service;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                static::getServiceFormField(),
                static::getServicePriceFormField(),
                static::getTimeOrderFormField(),
                static::getPeopleNumberFormField(),
                static::getSocialMediaFormField(),
                static::getSumFormField(),
                static::getCustomerFormField(),
                static::getEmployeeFormField(),
                static::getOptionsFormField(),
                Section::make('Оплата')
                    ->relationship('payment')
                    ->schema([
                        OrderResource::getPaymentDateFormField()->hidden(),
                        OrderResource::getPaymentCashAmountFormField(),
                        OrderResource::getPaymentCashlessAmountFormField(),
                    ])
                    ->columns(2)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['payment_date'] = now()->format('Y-m-d');

                        return $data;
                    }),
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
            ->default(now())
            ->label('Время')
            ->required()
            ->readOnly();
    }

    public static function getServiceFormField(): Select
    {
        return Select::make('service_id')
            ->relationship('service', 'name')
            ->label('Услуга')
            ->searchable()
            ->preload()
            ->live(onBlur: true)
            ->createOptionForm([
                Forms\Components\TextInput::make('name')
                    ->label('Название услуги')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('Описание')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label('Цена на одного человека')
                    ->maxLength(18)
                    ->required(),
            ])
            ->afterStateHydrated(function (Forms\Components\Select $component, $state, Set $set) {
                if ($state) {
                    $service = Service::find($state);
                    if ($service) {
                        $price = $service->price / 60;
                        $set('service_price', $price);
                    }
                }
            })
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                $price = 0;
                if ($state) {
                    $service = Service::find($state);
                    if ($service) {
                        $price = $service->price;
                        $set('service_price', $price);
                    }
                }
                if ($get('time_order') && $get('people_number')) {
                    $discount = $get('options.discount');
                    $prepayment = $get('options.prepayment');
                    $additional_discount = $get('options.additional_discount');
                    $sum = $price * $get('people_number') * $get('time_order') - $discount - $prepayment - $additional_discount;
                    $set('sum', $sum);
                    $set('payment.payment_cash_amount', $sum);
                }
            })
            ->required();
    }

    public static function getServicePriceFormField(): Hidden
    {
        return Hidden::make('service_price')
            ->default(0);
    }

    public static function getTimeOrderFormField(): TextInput
    {
        return TextInput::make('time_order')
            ->label('Время')
            ->numeric()
            ->minValue(1)
            ->maxValue(1440)
            ->live(onBlur: true)
            ->required()
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                if ($state && $get('people_number') && $get('service_price')) {
                    $discount = $get('options.discount');
                    $prepayment = $get('options.prepayment');
                    $additional_discount = $get('options.additional_discount');
                    $sum = $get('service_price') * $get('people_number') * $state - $discount - $prepayment - $additional_discount;
                    $set('sum', $sum);
                    $set('payment.payment_cash_amount', $sum);
                }
            });
    }

    public static function getPeopleNumberFormField(): TextInput
    {
        return TextInput::make('people_number')
            ->numeric()
            ->label('Количество человек')
            ->minValue(1)
            ->maxValue(100)
            ->live(onBlur: true)
            ->required()
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                if ($state && $get('time_order') && $get('service_price')) {
                    $discount = $get('options.discount');
                    $prepayment = $get('options.prepayment');
                    $additional_discount = $get('options.additional_discount');
                    $sum = $get('service_price') * $get('time_order') * $state - $discount - $prepayment - $additional_discount;
                    $set('sum', $sum);
                    $set('payment.payment_cash_amount', $sum);
                }
            });
    }

    public static function getSocialMediaFormField(): Select
    {
        return Forms\Components\Select::make('social_media_id')
            ->relationship('social_media', 'name')
            ->label('Откуда')
            ->searchable()
            ->preload()
            ->required()
            ->createOptionForm([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function getCustomerFormField(): Select
    {
        return Forms\Components\Select::make('customer_id')
            ->relationship('customer', 'name')
            ->label('Клиент')
            ->searchable()
            ->preload()
            ->live(onBlur: true)
            ->createOptionForm([
                Forms\Components\TextInput::make('name')
                    ->label('Ф.И.О.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required(),
            ]);
    }

    public static function getSumFormField(): TextInput
    {
        return TextInput::make('sum')
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
        return Forms\Components\DatePicker::make('payment_date')
            ->default(now())
            ->label('Дата')
            ->required();
    }

    public static function getPaymentCashAmountFormField(): TextInput
    {
        return Forms\Components\TextInput::make('payment_cash_amount')
            ->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $cashlessAmount = $get('payment_cashless_amount');
                    $sum = $get('../sum');
                    if (($cashlessAmount + $value) !== $sum) {
                        $fail('The total amount of payments does not match the order amount');
                    }
                },
            ])
            ->label('Наличные');
    }

    public static function getPaymentCashlessAmountFormField(): TextInput
    {
        return Forms\Components\TextInput::make('payment_cashless_amount')
            ->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $cashAmount = $get('payment_cash_amount');
                    $sum = $get('../sum');
                    if (($cashAmount + $value) !== $sum) {
                        $fail('The total amount of payments does not match the order amount');
                    }
                },
            ])
            ->default(0)
            ->label('Безналичные');
    }

    public static function getOptionsFormField(): Section
    {
        return Section::make()
            ->statePath('options')
            ->schema([
                TextInput::make('discount')
                    ->label('Скидка')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                        $discount = $state;
                        $prepayment = $get('prepayment');
                        $additional_discount = $get('additional_discount');
                        $sum = $get('../service_price') * $get('../time_order') * $get('../people_number') - $discount - $prepayment - $additional_discount;
                        $set('../sum', $sum);
                        $set('../payment.payment_cash_amount', $sum);
                    }),
                TextInput::make('prepayment')
                    ->label('Аванс')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                        $discount = $get('discount');
                        $prepayment = $state;
                        $additional_discount = $get('additional_discount');
                        $sum = $get('../service_price') * $get('../time_order') * $get('../people_number') - $discount - $prepayment - $additional_discount;
                        $set('../sum', $sum);
                        $set('../payment.payment_cash_amount', $sum);
                    }),
                TextInput::make('additional_discount')
                    ->label('Дополнительная скидка')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                        $discount = $get('discount');
                        $prepayment = $get('prepayment');
                        $additional_discount = $state;
                        $sum = $get('../service_price') * $get('../time_order') * $get('../people_number') - $discount - $prepayment - $additional_discount;
                        $set('../sum', $sum);
                        $set('../payment.payment_cash_amount', $sum);
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
                    ->date('H:i:s')
                    ->label('Время')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Услуга')
                    ->limit(22)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клиент')
                    ->limit(27)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_order')
                    ->numeric()
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
            ->filters([
                //
            ])
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
}
