<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Filament\Resources\PriceResource\RelationManagers\PriceItemsRelationManager;
use App\Models\Price;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('resources.price.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.price.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources.nav_groups.references');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('fields.service_name'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label(__('fields.description'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('fields.price'))
                    ->numeric()
                    ->maxLength(18)
                    ->required(),
                Forms\Components\Toggle::make('is_hidden')
                    ->label(__('fields.hidden'))
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('columns.service'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('columns.description'))
                    ->limit(25)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('columns.price'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_hidden')
                    ->label(__('columns.hidden')),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('messages.edit'))->hiddenLabel(true),
                Tables\Actions\DeleteAction::make()->label(__('messages.delete'))->hiddenLabel(true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('messages.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PriceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
