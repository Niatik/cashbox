<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Services\CashReportService;
use Illuminate\Support\Facades\DB;

final readonly class CalculateBalanceOnPaymentCreated
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
    public function handle(PaymentCreated $event): void
    {
        $payment = $event->payment;
        $this->cashReportService->updateOnPaymentCreated($payment);
    }
}
