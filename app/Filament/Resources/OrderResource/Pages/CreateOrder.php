<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);
        $data['employee_id'] = $user->employee->id;
        $data['order_date'] = now()->format('Y-m-d');
        $data['order_time'] = now()->format('H:i:s');

        return $data;
    }

    //protected function getRedirectUrl(): string
    //{
    //$orderId = $this->record->id;

    //return route('filament.admin.resources.payments.create', [
    //    'order_id' => $orderId,
    //    'order_sum' => $this->record->sum,
    //]);
    //}

    protected function getSteps(): array
    {
        return [
            Step::make('Услуга')
                ->description('Выберите услугу, время и количество людей')
                ->schema([
                    OrderResource::getDateFormField()->hidden(),
                    OrderResource::getTimeFormField()->readOnly(),
                    OrderResource::getPriceFormField(),
                    OrderResource::getTimeOrderFormField(),
                    OrderResource::getPeopleNumberFormField(),
                    OrderResource::getSocialMediaFormField(),
                    OrderResource::getSumFormField(),
                    OrderResource::getCustomerFormField(),
                    OrderResource::getEmployeeFormField(),
                    OrderResource::getOptionsFormField(),
                ]),
            Step::make('Оплата')
                ->description('Внесите оплату за услугу и завершите оформление')
                ->schema([
                    Section::make()
                        ->relationship('payment')
                        ->schema([
                            OrderResource::getPaymentDateFormField()->hidden(),
                            OrderResource::getPaymentCashAmountFormField(),
                            OrderResource::getPaymentCashlessAmountFormField(),
                        ])
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['payment_date'] = now()->format('Y-m-d');

                            return $data;
                        }),
                ]),
        ];
    }
}
