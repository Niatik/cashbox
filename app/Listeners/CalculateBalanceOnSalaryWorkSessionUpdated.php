<?php

namespace App\Listeners;

use App\Events\SalaryWorkSessionUpdated;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryWorkSessionUpdated
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
    public function handle(SalaryWorkSessionUpdated $event): void
    {
        $this->cashReportService->updateOnSalaryWorkSessionUpdated($event->salaryWorkSession);
    }
}
