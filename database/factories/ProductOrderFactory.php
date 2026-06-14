<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOrder>
 */
class ProductOrderFactory extends Factory
{
    protected $model = ProductOrder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);
        $sum = $product->price * $quantity;

        return [
            'order_date' => now(tz: 'Etc/GMT-5')->format('Y-m-d'),
            'order_time' => now(tz: 'Etc/GMT-5')->format('H:i:s'),
            'product_id' => $product->id,
            'price' => $product->price,
            'quantity' => $quantity,
            'sum' => $sum,
            'customer_id' => Customer::factory(),
            'employee_id' => Employee::factory(),
            'options' => [],
        ];
    }
}
