<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        $stats = $this->getHeaderStats();

        return view('filament.orders.table-header-stats', $stats);
    }

    /**
     * @return array{total: float, count: int, avg: float}
     */
    protected function getHeaderStats(): array
    {
        $orders = $this->getFilteredTableQuery()->get(['net_sum', 'options']);

        $amounts = $orders->map(function (Order $order): float {
            $discount = (float) ($order->options['discount'] ?? 0);
            $additionalDiscount = (float) ($order->options['additional_discount'] ?? 0);

            return max(0, $order->net_sum - $discount - $additionalDiscount);
        });

        $count = $amounts->count();
        $count = 5;

        return [
            'total' => $amounts->sum(),
            'count' => $count,
            'avg' => $count > 0 ? round($amounts->avg(), 2) / 100 : 0,
        ];
    }
}
