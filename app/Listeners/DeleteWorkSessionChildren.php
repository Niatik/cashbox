<?php

namespace App\Listeners;

use App\Events\WorkSessionDeleting;

class DeleteWorkSessionChildren
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(WorkSessionDeleting $event): void
    {
        foreach ($event->workSession->expenseWorkSessions as $expense) {
            $expense->delete();
        }

        foreach ($event->workSession->salaryWorkSessions as $salary) {
            $salary->delete();
        }
    }
}
