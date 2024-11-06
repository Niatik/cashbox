<?php

namespace App\Filament\Resources\PriceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_item')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('time_item')
                    ->required()
                    ->numeric(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_item')
            ->columns([
                Tables\Columns\TextColumn::make('name_item'),
                Tables\Columns\TextColumn::make('time_item'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
