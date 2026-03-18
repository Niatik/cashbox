<?php

namespace App\Filament\Resources\JobTitleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RatesRelationManager extends RelationManager
{
    protected static string $relationship = 'rates';

    public static function getTitle($ownerRecord, $pageClass): string
    {
        return __('resources.relation_managers.rates');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('rateRatios')
                    ->label(__('fields.ratios'))
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('ratio')
                            ->label(__('fields.ratio'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('ratio_from')
                            ->label(__('fields.from'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('ratio_to')
                            ->label(__('fields.to'))
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('columns.name'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading(__('messages.create_rate')),
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
