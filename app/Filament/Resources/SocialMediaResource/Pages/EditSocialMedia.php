<?php

namespace App\Filament\Resources\SocialMediaResource\Pages;

use App\Filament\Resources\SocialMediaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSocialMedia extends EditRecord
{
    protected static string $resource = SocialMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('messages.delete'))
                ->icon('heroicon-m-trash')
                ->before(function (Actions\DeleteAction $action) {
                    // Check if this social media has any orders
                    $ordersCount = $this->record->orders()->count();

                    if ($ordersCount > 0) {
                        Notification::make()
                            ->title(__('messages.cannot_delete_source'))
                            ->body(__('messages.source_in_use', ['count' => $ordersCount]))
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
        return __('messages.source_updated');
    }

    public function getTitle(): string
    {
        return __('messages.edit_source');
    }
}
