<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Database\Seeder;

class SalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates salary payments across the 30-day historical period.
     * salary_amount uses MoneyCast - pass dollar amounts.
     */
    public function run(): void
    {
        $employees = Employee::with('jobTitle')->get();

        $descriptions = [
            'Full shift payment',
            'Half shift payment',
            'Overtime payment',
            'Weekend bonus',
            'Holiday bonus',
        ];

        // Create salary payments for the past 30 days
        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();

            // Pay 2-3 employees per day (simulating shifts)
            $employeesToPay = $employees->random(rand(2, min(3, $employees->count())));

            foreach ($employeesToPay as $employee) {
                $isSenior = $employee->jobTitle?->title === 'Senior Operator';

                // Base salary depends on job title
                $baseSalary = $isSenior ? rand(80, 120) : rand(50, 80);

                // Add some variation
                $salary = $baseSalary + (rand(0, 99) / 100);

                $description = $descriptions[array_rand($descriptions)];

                Salary::create([
                    'salary_date' => $date,
                    'employee_id' => $employee->id,
                    'description' => $description,
                    'salary_amount' => $salary, // MoneyCast: dollar amount
                    'is_cash' => (bool) rand(0, 1),
                ]);
            }
        }
    }
}
