<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Rate;
use App\Models\SalaryRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkSession>
 */
class WorkSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => fake()->date(),
            'time' => fake()->time(),
            'salary_rate_id' => SalaryRate::factory(),
            'rate_id' => Rate::factory(),
        ];
    }
}
