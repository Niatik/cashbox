<?php

namespace App\Filament\Resources\SocialMediaResource\Pages;

use App\Filament\Resources\SocialMediaResource;
use App\Models\SocialMedia;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSocialMedia extends EditRecord
{
    protected static string $resource = SocialMediaResource::class;
    
    protected static ?string $title = 'Редактировать источник';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->icon('heroicon-m-trash')
                ->before(function (Actions\DeleteAction $action) {
                    // Check if this social media has any orders
                    $ordersCount = $this->record->orders()->count();
                    
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
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Источник успешно обновлен';
    }
    
    public function getTitle(): string
    {
        return 'Редактировать источник';
    }
}
