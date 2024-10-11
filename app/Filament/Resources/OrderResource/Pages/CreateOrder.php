<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);
        $data['employee_id'] = $user->employee->id;
        $data['order_date'] = now()->format('Y-m-d');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $orderId = $this->record->id;

        return route('filament.admin.resources.payments.create', [
            'order_id' => $orderId,
            'order_sum' => $this->record->sum,
        ]);
    }
}
