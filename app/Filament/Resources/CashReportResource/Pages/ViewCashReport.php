<?php

namespace App\Filament\Resources\CashReportResource\Pages;

use App\Filament\Resources\CashReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCashReport extends ViewRecord
{
    protected static string $resource = CashReportResource::class;

    public function getTitle(): string
    {
        return __('messages.daily_report');
    }
}
