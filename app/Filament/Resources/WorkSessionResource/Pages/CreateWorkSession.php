<?php

namespace App\Filament\Resources\WorkSessionResource\Pages;

use App\Filament\Resources\WorkSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkSession extends CreateRecord
{
    protected static string $resource = WorkSessionResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Старт'),
            $this->getCancelFormAction()->label('Отмена'),
        ];
    }
}
