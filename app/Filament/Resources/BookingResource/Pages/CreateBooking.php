<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public ?string $date = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);
        $data['employee_id'] = $user->employee->id;

        return $data;
    }

    public function mount(?string $date = null): void
    {
        parent::mount();

        $this->date = $date;
        $user = User::find(auth()->user()->id);

        if ($this->date) {
            $this->form->fill([
                'booking_date' => $this->date,
                'booking_time' => now()->format('H:i:s'),
                'employee_id' => $user->employee->id,
            ]);
        }
    }
}
