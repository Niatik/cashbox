<?php

namespace App\Listeners;

use App\Events\ExpenseDeleted;
use App\Services\CashReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CalculateBalanceOnExpenseDeleted
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
    public function handle(ExpenseDeleted $event): void
    {
        $expense = $event->expense;
        $this->cashReportService->updateOnExpenseDeleted($expense);
    }
}
