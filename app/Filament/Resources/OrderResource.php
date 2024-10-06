<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Service;
use Filament\Forms;
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

    protected static ?string $pluralLabel = 'Заказы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date_order')
                    ->default(now())
                    ->label('Дата')
                    ->required()
                    ->maxDate(now()),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Услуга')
                    ->searchable()
                    ->preload()
                    ->live()
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
                                $price = $service->price;
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
                            $set('sum', $price * $get('people_number') * $get('time_order'));
                        }
                    })
                    ->required(),
                Forms\Components\Hidden::make('service_price')
                    ->default(0),
                Forms\Components\TextInput::make('time_order')
                    ->label('Время')
                    ->numeric()
                    ->step(15)
                    ->minValue(15)
                    ->maxValue(1440)
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                        if ($state && $get('people_number') && $get('service_price')) {
                            $set('sum', $get('service_price') * $get('people_number') * $state);
                        }
                    }),
                Forms\Components\TextInput::make('people_number')
                    ->numeric()
                    ->label('Количество человек')
                    ->minValue(1)
                    ->maxValue(100)
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                        if ($state && $get('time_order') && $get('service_price')) {
                            $set('sum', $get('service_price') * $get('time_order') * $state);
                        }
                    }),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->default('pending')
                    ->options([
                        'pending' => 'Ожидает',
                        'advance' => 'Аванс',
                        'completed' => 'Оплачен',
                        'cancelled' => 'Отменен',
                    ])
                    ->required(),
                Forms\Components\Select::make('social_media_id')
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
                    ]),
                Forms\Components\TextInput::make('sum')
                    ->numeric()
                    ->label('Сумма')
                    ->default(0)
                    ->live()
                    ->readOnly(),
                Forms\Components\TextInput::make('employee_id')
                    ->hidden(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('date_order')
                    ->date('d.m.Y')
                    ->label('Дата')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Услуга')
                    ->limit(22)
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
                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->sortable()
                    ->options([
                        'pending' => 'Ожидает',
                        'advance' => 'Аванс',
                        'completed' => 'Оплачен',
                        'cancelled' => 'Отменен',
                    ]),
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
