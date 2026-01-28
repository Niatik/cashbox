<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected bool $shouldSaveAsDraft = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
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
                    $this->create();
                }),
        ];
    }

    public ?string $date = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);
        $data['employee_id'] = $user->employee->id;

        if (empty($data['customer_id']) && ! empty($data['customer_phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $data['customer_phone']],
                ['name' => $data['customer_name'] ?? null]
            );
            $data['customer_id'] = $customer->id;
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
