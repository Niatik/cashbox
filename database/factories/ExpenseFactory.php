<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_date' => $this->faker->date(),
            'expense_type_id' => ExpenseType::factory(),
            'description' => $this->faker->text(maxNbChars: 50),
            'expense_amount' => $this->faker->numberBetween(1000000, 10000000),
            'is_cash' => true,
        ];
    }
}
