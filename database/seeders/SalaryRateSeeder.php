<?php

namespace Database\Seeders;

use App\Models\JobTitle;
use App\Models\SalaryRate;
use Illuminate\Database\Seeder;

class SalaryRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * SalaryRate salary uses MoneyCast - pass dollar amounts
     */
    public function run(): void
    {
        $seniorJobTitle = JobTitle::where('title', 'Senior Operator')->first();
        $juniorJobTitle = JobTitle::where('title', 'Junior Operator')->first();

        $salaryRates = [
            // Senior Operator rates
            [
                'name' => 'Half Shift',
                'salary' => 50.00, // MoneyCast: $50.00
                'job_title_id' => $seniorJobTitle->id,
            ],
            [
                'name' => 'Full Shift',
                'salary' => 100.00, // MoneyCast: $100.00
                'job_title_id' => $seniorJobTitle->id,
            ],
            // Junior Operator rates
            [
                'name' => 'Half Shift',
                'salary' => 35.00, // MoneyCast: $35.00
                'job_title_id' => $juniorJobTitle->id,
            ],
            [
                'name' => 'Full Shift',
                'salary' => 70.00, // MoneyCast: $70.00
                'job_title_id' => $juniorJobTitle->id,
            ],
        ];

        foreach ($salaryRates as $rate) {
            SalaryRate::create($rate);
        }
    }
}
