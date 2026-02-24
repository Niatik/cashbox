<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use App\Models\WorkSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseWorkSession>
 */
class ExpenseWorkSessionFactory extends Factory
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
            'expense_type_id' => ExpenseType::factory(),
            'amount' => fake()->numberBetween(100, 10000),
        ];
    }
}
