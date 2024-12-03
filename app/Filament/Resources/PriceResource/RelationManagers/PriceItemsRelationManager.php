<?php

namespace App\Filament\Resources\PriceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceItems';
    protected static ?string $title = 'Коэффициенты';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_item')
                    ->label('Описание')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateHydrated(function (?string $state, Get $get, Set $set) {
                        if ($state) {
                            $set('factor', floatval($state));
                        }
                    })
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        if ($state) {
                            $set('factor', floatval($state));
                        }
                    }),
                Forms\Components\TextInput::make('factor')
                    ->label('Коэффициент')
                    ->required()
                    ->numeric(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('name_item')
            ->columns([
                Tables\Columns\TextColumn::make('name_item')
                    ->label('Описание')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factor')
                    ->label('Коэффициент')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Создание коэффициента'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
