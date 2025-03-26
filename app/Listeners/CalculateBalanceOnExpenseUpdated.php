<?php

namespace App\Listeners;

use App\Events\ExpenseUpdated;
use App\Services\CashReportService;

class CalculateBalanceOnExpenseUpdated
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
    public function handle(ExpenseUpdated $event): void
    {
        $expense = $event->expense;
        $this->cashReportService->updateOnExpenseUpdated($expense);
    }
}
