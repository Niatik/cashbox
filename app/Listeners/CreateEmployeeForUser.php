<?php

namespace App\Listeners;

use App\Models\Employee;
use App\Models\User;

class CreateEmployeeForUser
{
    /**
     * Create the event listener.
     */
    public function __construct(User $user)
    {
        $employee = Employee::firstOrNew([
            'user_id' => $user->id,
            //'name' => $user->name,
            //'salary' => 0,
            //'employment_date' => now(),
        ]);
        $employee->name = $user->name;
        $employee->phone = '';
        $employee->salary = 0;
        $employee->employment_date = now();
        $employee->save();
        //$employee = Employee::create([
        //    'user_id' => $user->id,
        //    'name' => $user->name,
        //    'phone' => '',
        //    'salary' => 0,
        //    'employment_date' => now(),
        //]);
        $user->assignRole('employee');
    }
}
