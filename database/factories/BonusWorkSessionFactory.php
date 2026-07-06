<?php

namespace Database\Factories;

use App\Models\BonusWorkSession;
use App\Models\WorkSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BonusWorkSession>
 */
class BonusWorkSessionFactory extends Factory
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
            'amount' => fake()->numberBetween(100, 10000),
            'bonus_type' => fake()->optional()->word(),
        ];
    }
}
