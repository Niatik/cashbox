<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkSessions extends ListRecords
{
    protected static string $resource = WorkSessionResource::class;

    public function mount(): void
    {
        parent::mount();

        // Set default filter to today only on fresh page load (no URL params)
        if (! request()->has('tableFilters')) {
            $this->tableFilters['date']['date'] = now()->toDateString();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
