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

    protected static ?string $navigationLabel = 'Источники';

    protected static ?string $label = 'Источник';

    protected static ?string $pluralLabel = 'Источники';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об источнике')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название источника')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Instagram, WhatsApp, Телефон')
                            ->unique(ignoreRecord: true)
                            ->helperText('Укажите название источника, откуда приходят клиенты'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название источника')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Количество заказов')
                    ->getStateUsing(fn (SocialMedia $record) => $record->orders()->count())
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? $state : 'Нет заказов'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->getStateUsing(fn (SocialMedia $record) => $record->orders()->count() > 0 ? 'Используется' : 'Можно удалить')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Используется' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Изменить')
                    ->icon('heroicon-m-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить')
                    ->icon('heroicon-m-trash')
                    ->before(function (Tables\Actions\DeleteAction $action, SocialMedia $record) {
                        // Check if this social media has any orders
                        $ordersCount = $record->orders()->count();

                        if ($ordersCount > 0) {
                            Notification::make()
                                ->title('Невозможно удалить источник')
                                ->body("Этот источник используется в {$ordersCount} заказах. Сначала удалите или измените источник в связанных заказах.")
                                ->danger()
                                ->send();

                            // Cancel the deletion
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные')
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            // Check if any of the selected records have orders
                            $recordsWithOrders = collect($records)->filter(function ($record) {
                                return $record->orders()->count() > 0;
                            });

                            if ($recordsWithOrders->count() > 0) {
                                $names = $recordsWithOrders->pluck('name')->join(', ');

                                Notification::make()
                                    ->title('Невозможно удалить источники')
                                    ->body("Источники '{$names}' используются в заказах. Сначала удалите или измените источники в связанных заказах.")
                                    ->danger()
                                    ->send();

                                // Cancel the deletion
                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Нет источников')
            ->emptyStateDescription('Создайте первый источник, чтобы отслеживать откуда приходят клиенты.')
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

    public static function getNavigationLabel(): string
    {
        return 'Источники';
    }

    public static function getModelLabel(): string
    {
        return 'Источник';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Источники';
    }
}
