<?php

namespace App\Filament\Resources\SocialMediaResource\Pages;

use App\Filament\Resources\SocialMediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSocialMedia extends ListRecords
{
    protected static string $resource = SocialMediaResource::class;
    
    protected static ?string $title = 'Источники клиентов';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Создать источник')
                ->icon('heroicon-m-plus'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Источники клиентов';
    }
}
