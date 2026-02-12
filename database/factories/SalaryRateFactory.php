<?php

namespace Database\Factories;

use App\Models\JobTitle;
use App\Models\Rate;
use App\Models\SalaryRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryRate>
 */
class SalaryRateFactory extends Factory
{
    protected $model = SalaryRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_title_id' => JobTitle::factory(),
            'rate_id' => Rate::factory(),
        ];
    }
}
