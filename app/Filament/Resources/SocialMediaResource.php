<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialMediaResource\Pages;
use App\Models\SocialMedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialMediaResource extends Resource
{
    protected static ?string $model = SocialMedia::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('resources.social_media.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('resources.social_media.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.social_media.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('fields.source_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('fields.source_name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('messages.source_placeholder'))
                            ->unique(ignoreRecord: true)
                            ->helperText(__('messages.source_helper')),
                        Forms\Components\Toggle::make('is_hidden')
                            ->label(__('fields.hidden'))
                            ->default(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('columns.source_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('columns.orders_count'))
                    ->getStateUsing(fn (SocialMedia $record) => $record->orders()->count())
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? $state : __('messages.no_orders')),

                Tables\Columns\TextColumn::make('people_count')
                    ->label(__('columns.people'))
                    ->getStateUsing(fn (SocialMedia $record) => $record->orders()->where('people_number', '<=', 1000)->sum('people_number'))
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('columns.status'))
                    ->getStateUsing(fn (SocialMedia $record) => $record->orders()->count() > 0 ? __('messages.in_use') : __('messages.can_delete'))
                    ->badge()
                    ->color(fn (string $state): string => $state === __('messages.in_use') ? 'warning' : 'success'),

                Tables\Columns\ToggleColumn::make('is_hidden')
                    ->label(__('columns.hidden')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('messages.edit'))
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label(__('messages.delete'))
                    ->icon('heroicon-m-trash')
                    ->before(function (Tables\Actions\DeleteAction $action, SocialMedia $record) {
                        $ordersCount = $record->orders()->count();

                        if ($ordersCount > 0) {
                            Notification::make()
                                ->title(__('messages.cannot_delete_source'))
                                ->body(__('messages.source_in_use', ['count' => $ordersCount]))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('messages.delete_selected'))
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $recordsWithOrders = collect($records)->filter(function ($record) {
                                return $record->orders()->count() > 0;
                            });

                            if ($recordsWithOrders->count() > 0) {
                                $names = $recordsWithOrders->pluck('name')->join(', ');

                                Notification::make()
                                    ->title(__('messages.cannot_delete_source'))
                                    ->body(__('messages.sources_in_use', ['names' => $names]))
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading(__('messages.no_sources'))
            ->emptyStateDescription(__('messages.no_sources_description'))
            ->emptyStateIcon('heroicon-o-megaphone')
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListSocialMedia::route('/'),
            'create' => Pages\CreateSocialMedia::route('/create'),
            'edit' => Pages\EditSocialMedia::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
