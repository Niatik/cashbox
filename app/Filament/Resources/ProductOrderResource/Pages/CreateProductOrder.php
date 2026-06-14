<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Filament\Resources\ProductOrderResource;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\CreateRecord;

class CreateProductOrder extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = ProductOrderResource::class;

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

        return $data;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Товар')
                ->description('Выберите товар и количество')
                ->schema([
                    ProductOrderResource::getDateFormField(),
                    ProductOrderResource::getTimeFormField()->readOnly(),
                    ProductOrderResource::getProductFormField(),
                    ProductOrderResource::getPriceFormField(),
                    ProductOrderResource::getQuantityFormField(),
                    ProductOrderResource::getNetSumFormField(),
                    ProductOrderResource::getSumFormField(),
                    ProductOrderResource::getCustomerFormField(),
                    ProductOrderResource::getEmployeeFormField(),
                    ProductOrderResource::getOptionsFormField(),
                ]),
            Step::make('Оплата')
                ->description('Внесите оплату за товар и завершите оформление')
                ->schema([
                    Section::make()
                        ->schema([
                            Repeater::make('payments')
                                ->label('Список оплат')
                                ->addable(false)
                                ->relationship()
                                ->schema([
                                    ProductOrderResource::getPaymentDateFormField()->hidden(),
                                    ProductOrderResource::getPaymentCashAmountFormField(),
                                    ProductOrderResource::getPaymentCashlessAmountFormField(),
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
