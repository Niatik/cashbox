<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Salary>
 */
class SalaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salary_date' => $this->faker->date(),
            'employee_id' => Employee::factory(),
            'salary_amount' => $this->faker->numberBetween(1000000, 10000000),
        ];
    }
}
