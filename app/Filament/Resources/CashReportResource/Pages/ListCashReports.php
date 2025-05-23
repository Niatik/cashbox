<?php

namespace App\Filament\Resources\CashReportResource\Pages;

use App\Filament\Resources\CashReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashReports extends ListRecords
{
    protected static string $resource = CashReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
