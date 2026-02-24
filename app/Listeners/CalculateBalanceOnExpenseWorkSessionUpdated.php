<?php

namespace App\Listeners;

use App\Events\ExpenseWorkSessionUpdated;
use App\Services\CashReportService;

class CalculateBalanceOnExpenseWorkSessionUpdated
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
    public function handle(ExpenseWorkSessionUpdated $event): void
    {
        $this->cashReportService->updateOnExpenseWorkSessionUpdated($event->expenseWorkSession);
    }
}
