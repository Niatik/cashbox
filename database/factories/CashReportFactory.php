<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashReport>
 */
class CashReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'morning_cash_balance' => $this->faker->numberBetween(10000, 1000000),
            'cash_income' => $this->faker->numberBetween(10000, 1000000),
            'cashless_income' => $this->faker->numberBetween(10000, 1000000),
            'cash_expense' => $this->faker->numberBetween(10000, 1000000),
            'cashless_expense' => $this->faker->numberBetween(10000, 1000000),
            'cash_salary' => $this->faker->numberBetween(10000, 1000000),
            'cashless_salary' => $this->faker->numberBetween(10000, 1000000),
        ];
    }
}
