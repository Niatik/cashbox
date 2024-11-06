<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceItem>
 */
class PriceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->numberBetween(10, 100);
        return [
            'price_id' => Price::factory(),
            'name_item' => $number.' minutes',
            'time_item' => $number,
        ];
    }
}
