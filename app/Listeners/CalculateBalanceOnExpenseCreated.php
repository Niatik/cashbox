<?php

namespace App\Listeners;

use App\Events\ExpenseCreated;
use App\Services\CashReportService;

class CalculateBalanceOnExpenseCreated
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
    public function handle(ExpenseCreated $event): void
    {
        $expense = $event->expense;
        $this->cashReportService->updateOnExpenseCreated($expense);
    }
}
