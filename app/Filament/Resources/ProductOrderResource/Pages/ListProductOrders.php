<?php

namespace App\Filament\Resources\ProductOrderResource\Pages;

use App\Filament\Resources\ProductOrderResource;
use App\Models\ProductOrder;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListProductOrders extends ListRecords
{
    protected static string $resource = ProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        $stats = $this->getHeaderStats();

        return view('filament.product-orders.table-header-stats', $stats);
    }

    /**
     * @return array{total: float, count: int, avg: float}
     */
    protected function getHeaderStats(): array
    {
        $orders = $this->getFilteredTableQuery()->get(['price', 'quantity', 'options']);

        $amounts = $orders->map(function (ProductOrder $order): float {
            $discount = (float) ($order->options['discount'] ?? 0);
            $additionalDiscount = (float) ($order->options['additional_discount'] ?? 0);

            return max(0, $order->net_sum - $discount - $additionalDiscount);
        });

        $count = $amounts->count();

        return [
            'total' => $amounts->sum(),
            'count' => $count,
            'avg' => $count > 0 ? round($amounts->avg(), 2) / 100 : 0,
        ];
    }
}
