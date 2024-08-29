<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = Service::factory()->create();
        $peopleNumber = $this->faker->numberBetween(1, 10);
        $timeOrder = $this->faker->numberBetween(15, 60);
        $sum = $service->price * $peopleNumber * $timeOrder;
        return [
            'date_order' => $this->faker->date(),
            'service_id' => $service->id,
            'social_media_id' => SocialMedia::factory(),
            'time_order' => $timeOrder,
            'people_number' => $peopleNumber,
            'status' => $this->faker->randomElement(['advance', 'completed', 'cancelled']),
            'sum' => $sum,
        ];
    }
}
