<?php

namespace App\Listeners;

use App\Events\ExpenseWorkSessionCreated;
use App\Services\CashReportService;

class CalculateBalanceOnExpenseWorkSessionCreated
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
    public function handle(ExpenseWorkSessionCreated $event): void
    {
        $this->cashReportService->updateOnExpenseWorkSessionCreated($event->expenseWorkSession);
    }
}
