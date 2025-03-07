<?php

namespace App\Listeners;

use App\Models\Employee;
use App\Models\User;

class CreateEmployeeForUser
{
    /**
     * Create the event listener.
     */
    public function __construct(User $user) {}

    public function handle(object $event): void
    {
        $user = $event->user;
        $employee = Employee::firstOrNew([
            'user_id' => $user->id,
        ]);
        $employee->name = $user->name;
        $employee->phone = '';
        $employee->salary = 0;
        $employee->employment_date = now();
        $employee->save();
        $user->assignRole('employee');
    }
}
