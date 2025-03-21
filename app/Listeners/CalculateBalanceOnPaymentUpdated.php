<?php

namespace App\Listeners;

use App\Events\PaymentUpdated;
use App\Services\CashReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CalculateBalanceOnPaymentUpdated
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
    public function handle(PaymentUpdated $event): void
    {
        $payment = $event->payment;
        $this->cashReportService->updateOnPaymentUpdated($payment);
    }
}
