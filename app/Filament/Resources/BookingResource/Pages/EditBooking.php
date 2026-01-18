<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['remaining'] = $data['sum'] - $data['prepayment'];

        $customer = $this->record->customer;

        $data['customer_name'] = $customer?->name ?? '';
        $data['customer_phone'] = $this->record->customer_phone ?? $customer?->phone;

        return $data;
    }
}
