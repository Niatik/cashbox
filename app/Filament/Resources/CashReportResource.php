<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashReportResource\Pages;
use App\Models\CashReport;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CashReportResource extends Resource
{
    protected static ?string $model = CashReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Касса';

    protected static ?string $modelLabel = 'Касса';

    protected static ?string $pluralModelLabel = 'Касса';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Основная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('date')
                            ->label('Дата')
                            ->date('d.m.Y'),
                        Infolists\Components\TextEntry::make('morning_cash_balance')
                            ->label('Баланс на начало дня (наличные)')
                            ->money('KZT'),
                    ])->columns(2),

                Infolists\Components\Section::make('Доходы')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_income')
                            ->label('Доход общий')
                            ->money('KZT')
                            ->getStateUsing(fn (Model $record) => $record->cash_income + $record->cashless_income),
                        Infolists\Components\TextEntry::make('cash_income')
                            ->label('Доход наличными')
                            ->money('KZT'),
                        Infolists\Components\TextEntry::make('cashless_income')
                            ->label('Доход безналичный')
                            ->money('KZT'),
                    ])->columns(3),

                Infolists\Components\Section::make('Расходы')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_expense')
                            ->label('Расход общий')
                            ->money('KZT')
                            ->getStateUsing(fn (Model $record) => $record->cash_expense + $record->cashless_expense),
                        Infolists\Components\TextEntry::make('cash_expense')
                            ->label('Расход наличными')
                            ->money('KZT'),
                        Infolists\Components\TextEntry::make('cashless_expense')
                            ->label('Расход безналичный')
                            ->money('KZT'),
                    ])->columns(3),

                Infolists\Components\Section::make('Зарплаты')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_salary')
                            ->label('Зарплата общая')
                            ->money('KZT')
                            ->getStateUsing(fn (Model $record) => $record->cash_salary + $record->cashless_salary),
                        Infolists\Components\TextEntry::make('cash_salary')
                            ->label('Зарплата наличными')
                            ->money('KZT'),
                        Infolists\Components\TextEntry::make('cashless_salary')
                            ->label('Зарплата безналичная')
                            ->money('KZT'),
                    ])->columns(3),

                Infolists\Components\Section::make('Итоговый баланс')
                    ->schema([
                        Infolists\Components\TextEntry::make('evening_cash_balance')
                            ->label('Остаток наличными')
                            ->money('KZT')
                            ->getStateUsing(fn (Model $record) => $record->morning_cash_balance + $record->cash_income - $record->cash_expense - $record->cash_salary
                            ),
                        Infolists\Components\TextEntry::make('evening_cashless_balance')
                            ->label('Остаток безналичный')
                            ->money('KZT')
                            ->getStateUsing(fn (Model $record) => $record->cashless_income - $record->cashless_expense - $record->cashless_salary
                            ),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('morning_cash_balance')
                    ->label('Баланс на начало дня (наличные)')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('total_income')
                    ->label('Доход общий')
                    ->money('kZT')
                    ->sortable()
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_income + $record->cashless_income;
                    }),

                TextColumn::make('cash_income')
                    ->label('Доход наличными')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('cashless_income')
                    ->label('Доход безналичный')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('total_expense')
                    ->label('Расход общий')
                    ->money('KZT')
                    ->sortable()
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_expense + $record->cashless_expense;
                    }),

                TextColumn::make('cash_expense')
                    ->label('Расход наличными')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('cashless_expense')
                    ->label('Расход безналичный')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('total_salary')
                    ->label('Зарплата общая')
                    ->money('KZT')
                    ->sortable()
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_salary + $record->cashless_salary;
                    }),

                TextColumn::make('cash_salary')
                    ->label('Зарплата наличными')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('cashless_salary')
                    ->label('Зарплата безналичная')
                    ->money('KZT')
                    ->sortable(),

                TextColumn::make('evening_cash_balance')
                    ->label('Остаток наличными')
                    ->money('KZT')
                    ->sortable()
                    ->getStateUsing(function (Model $record) {
                        return $record->morning_cash_balance + $record->cash_income - $record->cash_expense - $record->cash_salary;
                    }),

                TextColumn::make('evening_cashless_balance')
                    ->label('Остаток безналичный')
                    ->money('KZT')
                    ->sortable()
                    ->getStateUsing(function (Model $record) {
                        return $record->cashless_income - $record->cashless_expense - $record->cashless_salary;
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCashReports::route('/'),
            // 'create' => Pages\CreateCashReport::route('/create'),
            // 'edit' => Pages\EditCashReport::route('/{record}/edit'),
            'view' => Pages\ViewCashReport::route('/{record}'),
        ];
    }
}
