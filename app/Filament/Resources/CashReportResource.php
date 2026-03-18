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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CashReportResource extends Resource
{
    protected static ?string $model = CashReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('resources.cash_report.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('resources.cash_report.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.cash_report.plural');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('messages.basic_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('date')
                            ->label(__('fields.date'))
                            ->date('d.m.Y'),
                        Infolists\Components\TextEntry::make('morning_cash_balance')
                            ->label(__('fields.morning_cash_balance'))
                            ->numeric(decimalPlaces: 0),
                    ])->columns(2),

                Infolists\Components\Section::make(__('messages.income_section'))
                    ->schema([
                        Infolists\Components\TextEntry::make('total_income')
                            ->label(__('fields.total_income'))
                            ->numeric(decimalPlaces: 0)
                            ->getStateUsing(fn (Model $record) => $record->cash_income + $record->cashless_income),
                        Infolists\Components\TextEntry::make('cash_income')
                            ->label(__('fields.cash_income'))
                            ->numeric(decimalPlaces: 0),
                        Infolists\Components\TextEntry::make('cashless_income')
                            ->label(__('fields.cashless_income'))
                            ->numeric(decimalPlaces: 0),
                    ])->columns(3),

                Infolists\Components\Section::make(__('messages.expense_section'))
                    ->schema([
                        Infolists\Components\TextEntry::make('total_expense')
                            ->label(__('fields.total_expense'))
                            ->numeric(decimalPlaces: 0)
                            ->getStateUsing(fn (Model $record) => $record->cash_expense + $record->cashless_expense),
                        Infolists\Components\TextEntry::make('cash_expense')
                            ->label(__('fields.cash_expense'))
                            ->numeric(decimalPlaces: 0),
                        Infolists\Components\TextEntry::make('cashless_expense')
                            ->label(__('fields.cashless_expense'))
                            ->numeric(decimalPlaces: 0),
                    ])->columns(3),

                Infolists\Components\Section::make(__('messages.salary_section'))
                    ->schema([
                        Infolists\Components\TextEntry::make('total_salary')
                            ->label(__('fields.total_salary'))
                            ->numeric(decimalPlaces: 0)
                            ->getStateUsing(fn (Model $record) => $record->cash_salary + $record->cashless_salary),
                        Infolists\Components\TextEntry::make('cash_salary')
                            ->label(__('fields.cash_salary'))
                            ->numeric(decimalPlaces: 0),
                        Infolists\Components\TextEntry::make('cashless_salary')
                            ->label(__('fields.cashless_salary'))
                            ->numeric(decimalPlaces: 0),
                    ])->columns(3),

                Infolists\Components\Section::make(__('messages.final_balance'))
                    ->schema([
                        Infolists\Components\TextEntry::make('evening_cash_balance')
                            ->label(__('fields.evening_cash_balance'))
                            ->numeric(decimalPlaces: 0)
                            ->getStateUsing(fn (Model $record) => $record->morning_cash_balance + $record->cash_income - $record->cash_expense - $record->cash_salary
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(CashReport::where('date', '<=', now()->format('Y-m-d')))
            ->columns([
                TextColumn::make('date')
                    ->label(__('columns.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('morning_cash_balance')
                    ->label(__('columns.day_start'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('total_income')
                    ->label(__('columns.income'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("cash_income + cashless_income {$direction}");
                    })
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_income + $record->cashless_income;
                    }),

                TextColumn::make('cashless_income')
                    ->label(__('columns.cashless_income'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('evening_cash_balance')
                    ->label(__('columns.cash_balance'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("morning_cash_balance + cash_income - cash_expense - cash_salary {$direction}");
                    })
                    ->getStateUsing(function (Model $record) {
                        return $record->morning_cash_balance + $record->cash_income - $record->cash_expense - $record->cash_salary;
                    }),

                TextColumn::make('total_expense')
                    ->label(__('columns.expense'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("cash_expense + cashless_expense {$direction}");
                    })
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_expense + $record->cashless_expense;
                    }),

                TextColumn::make('total_salary')
                    ->label(__('columns.salary'))
                    ->numeric(decimalPlaces: 0)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("cash_salary + cashless_salary {$direction}");
                    })
                    ->getStateUsing(function (Model $record) {
                        return $record->cash_salary + $record->cashless_salary;
                    }),
            ])
            ->defaultSort('date', 'desc')
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
            'view' => Pages\ViewCashReport::route('/{record}'),
        ];
    }
}
