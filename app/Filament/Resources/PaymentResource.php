<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'sum')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\DatePicker::make('date_order')
                            ->default(now())
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название услуги')
                                    ->maxLength(255)
                                    ->required(),
                                Forms\Components\TextInput::make('description')
                                    ->label('Description')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->label('Цена на одного человека')
                                    ->maxLength(18)
                                    ->required(),
                            ])
                            ->afterStateUpdated(function(?int $state, Get $get, Set $set) {
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
                            ->numeric()
                            ->step(15)
                            ->maxValue(1440)
                            ->required()
                            ->afterStateUpdated(function(?int $state, Get $get, Set $set) {
                                if ($state && $get('people_number') && $get('service_price')) {
                                    $set('sum', $get('service_price') * $get('people_number') * $state);
                                }
                            }),
                        Forms\Components\TextInput::make('people_number')
                            ->numeric()
                            ->maxValue(100)
                            ->required()
                            ->afterStateUpdated(function(?int $state, Get $get, Set $set) {
                                if ($state && $get('time_order') && $get('service_price')) {
                                    $set('sum', $get('service_price') * $get('time_order') * $state);
                                }
                            }),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Ожидает',
                                'advance' => 'Аванс',
                                'completed' => 'Оплачен',
                                'cancelled' => 'Отменен',
                            ])
                            ->required(),
                        Forms\Components\Select::make('social_media_id')
                            ->relationship('social_media', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название')
                                    ->maxLength(255)
                                    ->required(),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('sum')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),
                    ])
                    ->required(),
                Forms\Components\Select::make('payment_type_id')
                    ->relationship('payment_type', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Способ оплаты')
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
                Forms\Components\TextInput::make('payment_amount')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
