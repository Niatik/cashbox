<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Price;
use App\Models\PriceItem;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = '';

    protected static ?string $pluralLabel = 'Бронирования';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getDateFormField(),
                static::getBookingPriceItemFormField(),
                static::getSumFormField(),
                static::getPrepaymentFormField(),
                static::getCustomerFormField(),
                static::getEmployeeFormField(),
            ])
            ->columns(1);
    }

    public static function getDateFormField(): DatePicker
    {
        return DatePicker::make('booking_date')
            ->default(now())
            ->label('Дата')
            ->required();
    }

    public static function getTimeFormField(): TimePicker
    {
        return TimePicker::make('booking_time')
            ->timezone('Etc/GMT-5')
            ->format('H:i')
            ->default(now())
            ->label('Время')
            ->required();
    }

    public static function getBookingPriceItemFormField(): Forms\Components\Repeater
    {
        return Forms\Components\Repeater::make('booking_price_items')
            ->schema([
                static::getTimeFormField(),
                static::getPriceFormField(),
                static::getPriceItemFormField(),
                static::getPeopleNumberFormField(),
                static::getNameOfPriceItemFormField(),
                static::getPeopleItemFormField(),
                static::getPrepaymentPriceItemFormField(),
                static::getIsCashFormField(),
            ])
            ->label('Услуги')
            ->collapsible()
            ->reorderableWithDragAndDrop(false)
            ->columns(3);
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

    public static function getPriceFormField(): Select
    {
        return Select::make('price_id')
            ->options(fn (Get $get): Collection => Price::query()
                ->orderBy('name')
                ->pluck('name', 'id'))

            ->label('Услуга')
            ->searchable()
            ->preload()
            ->live(debounce: 1000)
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                self::calcSum($get, $set);
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
            ->live()
            ->debounce()
            ->afterStateUpdated(function (Select $component, ?int $state, Get $get, Set $set) {
                self::calcSum($get, $set);
            });
    }

    public static function getPeopleNumberFormField(): TextInput
    {
        return TextInput::make('people_number')
            ->numeric()
            ->default(1)
            ->label('Количество человек')
            ->minValue(1)
            ->maxValue(100)
            ->live(debounce: 1000)
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                self::calcSum($get, $set);
            })
            ->hidden(fn (Get $get): bool => $get('name_item') == 'Количество человек');
    }

    public static function getSumFormField(): TextInput
    {
        return TextInput::make('sum')
            ->numeric()
            ->label('Сумма')
            ->default(0)
            ->readOnly();
    }

    public static function getPrepaymentFormField(): TextInput
    {
        return TextInput::make('prepayment')
            ->numeric()
            ->label('Предоплата')
            ->default(0)
            ->readOnly();
    }

    public static function getPrepaymentPriceItemFormField(): TextInput
    {
        return TextInput::make('prepayment_price_item')
            ->numeric()
            ->label('Предоплата')
            ->live(debounce: 1000)
            ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                self::calcSum($get, $set);
            });
    }

    public static function getIsCashFormField(): Toggle
    {
        return Toggle::make('is_cash')
            ->label('Наличные')
            ->default(false);
    }

    public static function getCustomerFormField(): Select
    {
        return Forms\Components\Select::make('customer_id')
            ->relationship('customer', 'name')
            ->label('Клиент')
            ->required()
            ->searchable()
            ->preload()
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

    public static function getEmployeeFormField(): TextInput
    {
        return TextInput::make('employee_id')
            ->hidden();
    }

    public static function calcSum(Get $get, Set $set): void
    {
        $bookingPriceItems = $get('../../booking_price_items');
        $sum = 0;
        $prepayment = 0;
        foreach ($bookingPriceItems as $bookingPriceItem) {
            $price = 0;
            $priceFactor = 0;
            $peopleNumber = 0;
            $prepaymentPriceItem = 0;
            if (Arr::exists($bookingPriceItem, 'price_id')) {
                $price = $bookingPriceItem['price_id'] ? Price::find($bookingPriceItem['price_id'])->price : 0;
            }
            if (Arr::exists($bookingPriceItem, 'price_item_id')) {
                $priceFactor = $bookingPriceItem['price_item_id'] ? PriceItem::find($bookingPriceItem['price_item_id'])->factor : 0;
            }
            if (Arr::exists($bookingPriceItem, 'people_number')) {
                $peopleNumber = $bookingPriceItem['people_number'] ?? 1;
            }
            if (Arr::exists($bookingPriceItem, 'prepayment_price_item')) {
                $prepaymentPriceItem = $bookingPriceItem['prepayment_price_item'] ?? 0;
            }
            $prepaymentPriceItem = intval(floatval($prepaymentPriceItem) * 100) / 100;
            $sum = $sum + $peopleNumber * $priceFactor * $price;
            $prepayment = $prepayment + $prepaymentPriceItem;
        }
        $set('../../sum', $sum);
        $set('../../prepayment', $prepayment);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                //$query->crossJoin('orders', 'orders.booking_id', '=', 'bookings.id'); //->ddRawSql();
                $query
                    ->join('customers', 'bookings.customer_id', '=', 'customers.id')
                    ->join('orders', 'bookings.id', '=', 'orders.booking_id')
                    ->join('prices', 'orders.price_id', '=', 'prices.id')
                    ->join('price_items', 'orders.price_item_id', '=', 'price_items.id')
                    ->select(
                        'bookings.id',
                        'bookings.booking_date',
                        'orders.order_time',
                        'prices.name as price_name',
                        'price_items.name_item',
                        'customers.name as customer_name',
                        'orders.people_number as people_number',
                        'orders.sum as order_sum',
                    );
            })
            ->columns([
                TextColumn::make('booking_date')
                    ->date('d.m.Y')
                    ->label('Дата')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_time')
                    ->date('H:i:s')
                    ->label('Время')
                    ->timezone('Etc/GMT-5')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_name')
                    ->label('Услуга')
                    ->limit(22)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->limit(27)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_item')
                    ->label('Время')
                    ->sortable(),
                Tables\Columns\TextColumn::make('people_number')
                    ->numeric()
                    ->label('Люди')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_sum')
                    ->numeric()
                    ->label('Сумма')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => $state / 100),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereDate('booking_date', '>', now(tz: 'Etc/GMT-5')->subDay());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create/{date?}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    protected static function getTableFilters(): array
    {
        return [
            Filter::make('selected_date')
                ->default(now())
                ->form([
                    DatePicker::make('select_date')
                        ->label('Выберите дату'),
                ])
                ->query(function (Builder $query, array $data, Get $get): Builder {
                    return $query
                        ->when(
                            $data['select_date'],
                            fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '=', $date),
                        );
                }),
        ];
    }
}
