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
use Illuminate\Support\Facades\DB;

class DraftBookingsTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('resources.draft_bookings.heading');
    }

    /**
     * Cache for price names to avoid repeated queries.
     */
    protected static ?Collection $priceCache = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Booking::query())
            ->queryStringIdentifier('drafts')
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->select([
                        'bookings.id',
                        'bookings.booking_date',
                        'bookings.sum',
                        DB::raw('jt.booking_time'),
                        DB::raw('jt.price_id'),
                        DB::raw('jt.people_number'),
                        DB::raw('jt.name_item'),
                        DB::raw('COALESCE(customers.name, customers.phone) as customer_name'),
                    ])
                    ->leftJoin('customers', 'bookings.customer_id', '=', 'customers.id')
                    ->crossJoin(DB::raw("JSON_TABLE(
                        bookings.booking_price_items,
                        '\$[*]' COLUMNS (
                            booking_time VARCHAR(10) PATH '\$.booking_time',
                            price_id INT PATH '\$.price_id',
                            people_number INT PATH '\$.people_number',
                            name_item VARCHAR(255) PATH '\$.name_item'
                        )
                    ) AS jt"))
                    ->where('bookings.is_draft', true)
                    ->whereDate('bookings.booking_date', '>=', now('Asia/Almaty')->startOfDay())
                    ->orderBy('bookings.booking_date', 'desc')
                    ->orderBy(DB::raw('jt.booking_time'));
            })
            ->columns([
                TextColumn::make('booking_date')
                    ->date('d.m.Y')
                    ->label(__('columns.date'))
                    ->sortable(),
                TextColumn::make('booking_time')
                    ->label(__('columns.time')),
                TextColumn::make('price_name')
                    ->label(__('columns.service'))
                    ->limit(22)
                    ->getStateUsing(fn ($record): ?string => $this->getPriceNames([$record->price_id])),
                TextColumn::make('customer_name')
                    ->label(__('columns.customer'))
                    ->limit(27),
                TextColumn::make('name_item')
                    ->label(__('columns.service_time')),
                TextColumn::make('people_number')
                    ->numeric()
                    ->label(__('columns.people')),
                TextColumn::make('sum')
                    ->numeric()
                    ->label(__('columns.sum'))
                    ->formatStateUsing(fn ($state): string => $state ? (string) $state : '0'),
            ])
            ->recordUrl(fn (Booking $record): string => BookingResource::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()->hiddenLabel(),
            ])
            ->emptyStateHeading(__('messages.no_drafts'))
            ->emptyStateDescription(__('messages.no_drafts_description'));
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
