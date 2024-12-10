<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Price;
use App\Models\PriceItem;
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
        $priceItem = PriceItem::factory([
            'price_id' => $price->id,
        ])->create();
        $peopleNumber = $this->faker->numberBetween(1, 10);
        $sum = $price->price * $peopleNumber * $priceItem->time_item;

        return [
            'order_date' => now(tz: 'Etc/GMT-5')->format('Y-m-d'), //$this->faker->date,
            'order_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'), //$this->faker->time,
            'price_id' => $price->id,
            'price_item_id' => $priceItem->id,
            'social_media_id' => SocialMedia::factory(),
            'people_number' => $peopleNumber,
            'sum' => $sum,
            'employee_id' => Employee::factory(),
            'customer_id' => Customer::factory(),
            'is_paid' => true,
        ];
    }
}
