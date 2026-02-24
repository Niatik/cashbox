<?php

namespace App\Listeners;

use App\Events\ExpenseWorkSessionDeleted;
use App\Services\CashReportService;

class CalculateBalanceOnExpenseWorkSessionDeleted
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
    public function handle(ExpenseWorkSessionDeleted $event): void
    {
        $this->cashReportService->updateOnExpenseWorkSessionDeleted($event->expenseWorkSession);
    }
}
