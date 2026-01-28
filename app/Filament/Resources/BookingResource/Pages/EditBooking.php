<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected bool $shouldSaveAsDraft = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

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
            Action::make('saveAsDraft')
                ->label('Сохранить черновик')
                ->color('gray')
                ->extraAttributes(['class' => 'ms-auto'])
                ->action(function () {
                    $this->shouldSaveAsDraft = true;
                    $this->save();
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['remaining'] = $data['sum'] - $data['prepayment'];

        $customer = $this->record->customer;

        $data['customer_name'] = $customer?->name ?? '';
        $data['customer_phone'] = $customer?->phone ?? ''; // Берем телефон только из связи

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['customer_id']) && ! empty($data['customer_phone'])) {
            Customer::where('id', $data['customer_id'])->update([
                'phone' => $data['customer_phone'],
                'name' => $data['customer_name'] ?? null,
            ]);
        }

        // Удаляем временные поля, которых нет в БД bookings
        unset($data['customer_phone'], $data['customer_name']);

        if ($this->shouldSaveAsDraft) {
            $data['is_draft'] = true;
        } else {
            $data['is_draft'] = false;
        }

        return $data;
    }
}
