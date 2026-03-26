<?php

namespace Database\Factories;

use App\Models\SalaryWorkSession;
use App\Models\WorkSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryWorkSession>
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
            'salary_amount_cashless' => 0,
        ];
    }

    /**
     * State for cash-only payment.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_amount' => fake()->numberBetween(500, 20000),
            'salary_amount_cashless' => 0,
        ]);
    }

    /**
     * State for cashless-only payment.
     */
    public function cashless(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_amount' => 0,
            'salary_amount_cashless' => fake()->numberBetween(500, 20000),
        ]);
    }

    /**
     * State for mixed payment (both cash and cashless).
     */
    public function mixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_amount' => fake()->numberBetween(250, 10000),
            'salary_amount_cashless' => fake()->numberBetween(250, 10000),
        ]);
    }
}
