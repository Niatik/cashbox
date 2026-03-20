<?php

namespace Database\Seeders;

use App\Models\JobTitle;
use App\Models\Rate;
use App\Models\RateRatio;
use Illuminate\Database\Seeder;

class RateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * RateRatio ratio uses MoneyCast - pass dollar amounts
     */
    public function run(): void
    {
        $seniorJobTitle = JobTitle::where('title', 'Senior Operator')->first();
        $juniorJobTitle = JobTitle::where('title', 'Junior Operator')->first();

        // Create rates for each job title
        foreach ([$seniorJobTitle, $juniorJobTitle] as $jobTitle) {
            $isSenior = $jobTitle->title === 'Senior Operator';

            // Weekdays rate
            $weekdaysRate = Rate::create([
                'name' => 'Weekdays',
                'job_title_id' => $jobTitle->id,
            ]);

            // Weekends rate
            $weekendsRate = Rate::create([
                'name' => 'Weekends',
                'job_title_id' => $jobTitle->id,
            ]);

            // Create rate ratios (income thresholds and corresponding salary percentages)
            // Weekdays ratios - lower rates
            $weekdayRatios = [
                ['ratio' => $isSenior ? 2.00 : 1.50, 'ratio_from' => 0, 'ratio_to' => 100],
                ['ratio' => $isSenior ? 2.50 : 2.00, 'ratio_from' => 100, 'ratio_to' => 200],
                ['ratio' => $isSenior ? 3.00 : 2.50, 'ratio_from' => 200, 'ratio_to' => 500],
            ];

            foreach ($weekdayRatios as $ratio) {
                RateRatio::create([
                    'rate_id' => $weekdaysRate->id,
                    'ratio' => $ratio['ratio'], // MoneyCast: dollar amounts
                    'ratio_from' => $ratio['ratio_from'],
                    'ratio_to' => $ratio['ratio_to'],
                ]);
            }

            // Weekends ratios - higher rates
            $weekendRatios = [
                ['ratio' => $isSenior ? 2.50 : 2.00, 'ratio_from' => 0, 'ratio_to' => 100],
                ['ratio' => $isSenior ? 3.00 : 2.50, 'ratio_from' => 100, 'ratio_to' => 200],
                ['ratio' => $isSenior ? 3.50 : 3.00, 'ratio_from' => 200, 'ratio_to' => 500],
            ];

            foreach ($weekendRatios as $ratio) {
                RateRatio::create([
                    'rate_id' => $weekendsRate->id,
                    'ratio' => $ratio['ratio'], // MoneyCast: dollar amounts
                    'ratio_from' => $ratio['ratio_from'],
                    'ratio_to' => $ratio['ratio_to'],
                ]);
            }
        }
    }
}
