<?php

namespace App\Listeners;

use App\Events\SalaryCreated;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryCreated
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
    public function handle(SalaryCreated $event): void
    {
        $salary = $event->salary;
        $this->cashReportService->updateOnSalaryCreated($salary);
    }
}
