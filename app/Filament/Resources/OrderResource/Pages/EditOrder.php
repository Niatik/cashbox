<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            Action::make('pay')
                ->action('pay')
                ->label('Оплатить'),
        ];
    }

    public function pay(): void
    {
        $orderId = $this->record->id;

        Notification::make()
            ->title('Redirecting to payment')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.resources.payments.create', [
            'order_id' => $orderId,
            'order_sum' => $this->record->sum,
        ]));
    }
}
