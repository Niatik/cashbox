<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seniorJobTitle = JobTitle::where('title', 'Senior Operator')->first();
        $juniorJobTitle = JobTitle::where('title', 'Junior Operator')->first();

        $employees = [
            [
                'name' => 'John',
                'fio' => 'John Smith',
                'phone' => '+1 (555) 123-4567',
                'email' => 'john.smith@example.com',
                'job_title_id' => $seniorJobTitle->id,
                'employment_date' => now()->subMonths(12)->toDateString(),
                'is_hidden' => false,
                'info' => 'Experienced senior operator, handles complex bookings',
            ],
            [
                'name' => 'Emily',
                'fio' => 'Emily Davis',
                'phone' => '+1 (555) 234-5678',
                'email' => 'emily.davis@example.com',
                'job_title_id' => $seniorJobTitle->id,
                'employment_date' => now()->subMonths(8)->toDateString(),
                'is_hidden' => false,
                'info' => 'Senior operator specializing in group bookings',
            ],
            [
                'name' => 'Michael',
                'fio' => 'Michael Brown',
                'phone' => '+1 (555) 345-6789',
                'email' => 'michael.brown@example.com',
                'job_title_id' => $juniorJobTitle->id,
                'employment_date' => now()->subMonths(4)->toDateString(),
                'is_hidden' => false,
                'info' => 'Junior operator, learning the system',
            ],
            [
                'name' => 'Sarah',
                'fio' => 'Sarah Wilson',
                'phone' => '+1 (555) 456-7890',
                'email' => 'sarah.wilson@example.com',
                'job_title_id' => $juniorJobTitle->id,
                'employment_date' => now()->subMonths(2)->toDateString(),
                'is_hidden' => false,
                'info' => 'Newest team member, excellent with customers',
            ],
        ];

        foreach ($employees as $employeeData) {
            $user = User::where('email', $employeeData['email'])->first();
            unset($employeeData['email']);

            Employee::create([
                ...$employeeData,
                'user_id' => $user?->id,
            ]);
        }
    }
}
