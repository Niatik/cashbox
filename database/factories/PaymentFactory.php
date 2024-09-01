<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->create(),
            'payment_type_id' => PaymentType::factory()->create(),
            'payment_date' => $this->faker->date(),
            'payment_amount' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}
