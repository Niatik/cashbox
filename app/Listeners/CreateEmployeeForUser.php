<?php

namespace App\Listeners;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateEmployeeForUser
{
    /**
     * Create the event listener.
     */
    public function __construct(User $user)
    {
        Employee::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => '',
            'salary' => 0,
            'employment_date' => now(),
        ]);
    }
}
