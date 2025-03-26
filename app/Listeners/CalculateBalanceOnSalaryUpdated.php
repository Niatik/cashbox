<?php

namespace App\Listeners;

use App\Events\SalaryUpdated;
use App\Services\CashReportService;

class CalculateBalanceOnSalaryUpdated
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
    public function handle(SalaryUpdated $event): void
    {
        $salary = $event->salary;
        $this->cashReportService->updateOnSalaryUpdated($salary);
    }
}
