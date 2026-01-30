<?php

namespace Database\Factories;

use App\Models\Rate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RateRatio>
 */
class RateRatioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rate_id' => Rate::factory(),
            'name' => fake()->words(2, true),
            'ratio' => fake()->randomFloat(2, 0.5, 2.0),
        ];
    }
}
