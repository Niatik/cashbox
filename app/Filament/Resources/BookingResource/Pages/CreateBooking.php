<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

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

    protected function beforeFill(): void
    {

        // Runs before the form fields are populated with their default values.
    }

    protected function afterFill(): void
    {
        //dd($this->form);
        // Runs after the form fields are populated with their default values.
    }

    protected function beforeValidate(): void
    {
        //dd($this->form->getRawState());
        // Runs before the form fields are validated when the form is submitted.
    }

    protected function afterValidate(): void
    {
        //dd($this->form->getRawState());
    }

    protected function beforeCreate(): void
    {
        //dd('kjkkk');
    }
}
