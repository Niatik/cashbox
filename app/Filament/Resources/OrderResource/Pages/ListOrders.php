<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        $query = $this->getFilteredTableQuery();
        return view('filament.orders.table-header-stats', [
            'total' => $query->sum('sum') / 100,
            'count' => $query->count(),
            'avg' => round($query->avg('sum'), 2) / 100,
        ]);
    }
}
