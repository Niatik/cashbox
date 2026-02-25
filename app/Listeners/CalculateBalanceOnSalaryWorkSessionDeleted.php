<?php

namespace App\Listeners;

use App\Events\SalaryWorkSessionDeleted;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryWorkSessionDeleted
{
    private CashReportService $cashReportService;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->cashReportService = new CashReportService;
    }

    /**
     * Handle the event.
     */
    public function handle(SalaryWorkSessionDeleted $event): void
    {
        $this->cashReportService->updateOnSalaryWorkSessionDeleted($event->salaryWorkSession);
    }
}
