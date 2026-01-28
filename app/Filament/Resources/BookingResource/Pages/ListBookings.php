<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(function () {
                    // Получаем значение из фильтра
                    $tableFilters = $this->getTableFiltersForm()->getRawState();
                    $selectedDate = $tableFilters['selected_date'] ?? null;

                    // Если дата выбрана, передаём её в URL
                    if ($selectedDate) {
                        return static::getResource()::getUrl('create', [
                            'date' => $selectedDate['select_date'],
                        ]);
                    }

                    return static::getResource()::getUrl('create');
                }),

        ];
    }
}
