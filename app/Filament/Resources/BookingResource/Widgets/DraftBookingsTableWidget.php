<?php

namespace App\Filament\Resources\BookingResource\Widgets;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Price;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DraftBookingsTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Черновики бронирований';

    /**
     * Cache for price names to avoid repeated queries.
     */
    protected static ?Collection $priceCache = null;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getBaseQuery())
            ->queryStringIdentifier('drafts')
            ->columns([
                TextColumn::make('booking_date')
                    ->date('d.m.Y')
                    ->label('Дата')
                    ->sortable(),
                TextColumn::make('booking_time_display')
                    ->label('Время')
                    ->getStateUsing(function (Booking $record): ?string {
                        $items = $record->booking_price_items ?? [];
                        if (empty($items)) {
                            return null;
                        }
                        $times = collect($items)->pluck('booking_time')->filter()->unique()->implode(', ');

                        return $times ?: null;
                    }),
                TextColumn::make('price_name_display')
                    ->label('Услуга')
                    ->limit(22)
                    ->getStateUsing(function (Booking $record): ?string {
                        $items = $record->booking_price_items ?? [];
                        if (empty($items)) {
                            return null;
                        }
                        $priceIds = collect($items)->pluck('price_id')->filter()->unique()->toArray();
                        if (empty($priceIds)) {
                            return null;
                        }

                        return $this->getPriceNames($priceIds);
                    }),
                TextColumn::make('customer_display')
                    ->label('Клиент')
                    ->limit(27)
                    ->getStateUsing(fn (Booking $record): ?string => $record->customer?->name ?? $record->customer?->phone),
                TextColumn::make('name_item_display')
                    ->label('Время')
                    ->getStateUsing(function (Booking $record): ?string {
                        $items = $record->booking_price_items ?? [];
                        if (empty($items)) {
                            return null;
                        }

                        return collect($items)->pluck('name_item')->filter()->unique()->implode(', ');
                    }),
                TextColumn::make('people_number_display')
                    ->label('Люди')
                    ->numeric()
                    ->getStateUsing(function (Booking $record): ?int {
                        $items = $record->booking_price_items ?? [];
                        if (empty($items)) {
                            return null;
                        }

                        return collect($items)->sum('people_number');
                    }),
                TextColumn::make('sum')
                    ->numeric()
                    ->label('Сумма')
                    ->formatStateUsing(fn ($state): string => $state ? (string) $state : '0'),
            ])
            ->recordUrl(fn (Booking $record): string => BookingResource::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()->hiddenLabel(),
            ])
            ->emptyStateHeading('Нет черновиков')
            ->emptyStateDescription('Черновики бронирований будут отображаться здесь');
    }

    protected function getBaseQuery(): Builder
    {
        return Booking::query()
            ->with(['customer'])
            ->where('is_draft', true)
            ->whereDate('booking_date', '>=', now('Asia/Almaty')->startOfDay())
            ->orderBy('booking_date', 'desc');
    }

    protected function getPriceNames(array $priceIds): ?string
    {
        if (self::$priceCache === null) {
            self::$priceCache = Price::pluck('name', 'id');
        }

        $names = collect($priceIds)
            ->map(fn ($id) => self::$priceCache->get($id))
            ->filter()
            ->unique()
            ->implode(', ');

        return $names ?: null;
    }
}
