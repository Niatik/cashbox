<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Price;
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
        $price = Price::factory()->create();
        $peopleNumber = $this->faker->numberBetween(1, 10);
        $timeOrder = $this->faker->numberBetween(15, 60);
        $sum = $price->price * $peopleNumber * $timeOrder;
        return [
            'order_date' => $this->faker->date,
            'order_time' => $this->faker->time,
            'price_id' => $price->id,
            'social_media_id' => SocialMedia::factory(),
            'time_order' => $timeOrder,
            'people_number' => $peopleNumber,
            'sum' => $sum,
            'employee_id' => Employee::factory(),
            'customer_id' => Customer::factory(),
        ];
    }
}
