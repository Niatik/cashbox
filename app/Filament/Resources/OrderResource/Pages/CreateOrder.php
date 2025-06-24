<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Filament\Resources\Resource;

class CreateOrder extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::find(auth()->user()->id);
        $data['employee_id'] = $user->employee->id;
        $data['order_date'] = $data['order_date'] ?? now()->format('Y-m-d');
        $data['order_time'] = $data['order_time'] ?? now()->format('H:i:s');
        if (! Arr::exists($data, 'people_number')) {
            $data['people_number'] = $data['people_item'];
        }

        return $data;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Услуга')
                ->description('Выберите услугу, время и количество людей')
                ->schema([
                    OrderResource::getDateFormField(),
                    OrderResource::getTimeFormField()->readOnly(),
                    OrderResource::getPriceFormField(),
                    OrderResource::getPriceItemFormField(),
                    OrderResource::getPriceValueFormField(),
                    OrderResource::getPriceFactorFormField(),
                    OrderResource::getPeopleNumberFormField(),
                    OrderResource::getPeopleItemFormField(),
                    OrderResource::getNameOfPriceItemFormField(),
                    OrderResource::getSocialMediaFormField(),
                    OrderResource::getNetSumFormField(),
                    OrderResource::getSumFormField(),
                    OrderResource::getCustomerFormField(),
                    OrderResource::getEmployeeFormField(),
                    OrderResource::getOptionsFormField(),
                ]),
            Step::make('Оплата')
                ->description('Внесите оплату за услугу и завершите оформление')
                ->schema([
                    Section::make()
                        ->schema([
                            Repeater::make('payments')
                                ->label('Список оплат')
                                ->addable(false)
                                ->relationship()
                                ->schema([
                                    OrderResource::getPaymentDateFormField()->hidden(),
                                    OrderResource::getPaymentCashAmountFormField(),
                                    OrderResource::getPaymentCashlessAmountFormField(),
                                ])
                                ->columns(3)
                                ->defaultItems(0)
                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire): array {
                                    $data['payment_date'] = $livewire->data['order_date'] ?? now()->format('Y-m-d');

                                    return $data;
                                }),
                        ]),
                ]),
        ];
    }
}
