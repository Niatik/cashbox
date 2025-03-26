<?php

namespace App\Listeners;

use App\Events\SalaryDeleted;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryDeleted
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
    public function handle(SalaryDeleted $event): void
    {
        $salary = $event->salary;
        $this->cashReportService->updateOnSalaryDeleted($salary);

    }
}
