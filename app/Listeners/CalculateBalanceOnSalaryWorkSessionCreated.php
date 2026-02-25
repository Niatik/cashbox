<?php

namespace App\Listeners;

use App\Events\SalaryWorkSessionCreated;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryWorkSessionCreated
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
    public function handle(SalaryWorkSessionCreated $event): void
    {
        $this->cashReportService->updateOnSalaryWorkSessionCreated($event->salaryWorkSession);
    }
}
