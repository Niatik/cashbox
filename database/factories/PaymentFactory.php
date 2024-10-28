<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
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
            'payment_date' => $this->faker->date(),
            'payment_cash_amount' => $this->faker->numberBetween(10000, 100000),
            'payment_cashless_amount' => $this->faker->numberBetween(10000, 100000),
        ];
    }
}
