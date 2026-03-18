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

    public static function getTitle($ownerRecord, $pageClass): string
    {
        return __('resources.relation_managers.price_items');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_item')
                    ->label(__('fields.description'))
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
                    ->label(__('fields.factor'))
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
                    ->label(__('columns.description'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factor')
                    ->label(__('fields.factor'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading(__('messages.create_factor')),
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
