<?php

namespace App\Listeners;

use App\Events\PaymentDeleted;
use App\Services\CashReportService;

class CalculateBalanceOnPaymentDeleted
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
    public function handle(PaymentDeleted $event): void
    {
        $payment = $event->payment;
        $this->cashReportService->updateOnPaymentDeleted($payment);
    }
}
