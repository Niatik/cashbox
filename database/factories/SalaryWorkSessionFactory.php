<?php

namespace Database\Factories;

use App\Models\WorkSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryWorkSession>
 */
class SalaryWorkSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_session_id' => WorkSession::factory(),
            'income_total' => fake()->numberBetween(1000, 50000),
            'expense_total' => fake()->numberBetween(100, 10000),
            'salary_total' => fake()->numberBetween(500, 20000),
            'salary_amount' => fake()->numberBetween(500, 20000),
            'is_cash' => fake()->boolean(),
        ];
    }
}
