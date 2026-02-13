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

    protected static ?string $title = 'Тарифы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Repeater::make('rateRatios')
                    ->label('Коэффициенты')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('ratio')
                            ->label('Коэффициент')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('ratio_from')
                            ->label('От')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ratio_to')
                            ->label('До')
                            ->required()
                            ->maxLength(255),
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
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Создание тарифа'),
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
