<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\PriceItem;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Price uses MoneyCast - pass dollar amounts (e.g., 20.00)
     * PriceItem factor uses ThousandthCast - pass decimal values (0.25, 0.5, 1.0, 1.5)
     */
    public function run(): void
    {
        // Standard pricing - $20/hour per person
        $standardPrice = Price::create([
            'name' => 'Standard',
            'description' => 'Standard rate for regular sessions',
            'price' => 20.00, // MoneyCast: $20.00
            'is_hidden' => false,
        ]);

        // Premium pricing - $30/hour per person
        $premiumPrice = Price::create([
            'name' => 'Premium',
            'description' => 'Premium rate for peak hours and weekends',
            'price' => 30.00, // MoneyCast: $30.00
            'is_hidden' => false,
        ]);

        // Group pricing - $15/hour per person
        $groupPrice = Price::create([
            'name' => 'Group',
            'description' => 'Discounted rate for groups of 5+',
            'price' => 15.00, // MoneyCast: $15.00
            'is_hidden' => false,
        ]);

        // Create price items for each price
        $priceItems = [
            ['name_item' => '15 min', 'factor' => 0.25],
            ['name_item' => '30 min', 'factor' => 0.5],
            ['name_item' => '1 hour', 'factor' => 1.0],
            ['name_item' => '1.5 hours', 'factor' => 1.5],
            ['name_item' => '2 hours', 'factor' => 2.0],
        ];

        foreach ([$standardPrice, $premiumPrice, $groupPrice] as $price) {
            foreach ($priceItems as $item) {
                PriceItem::create([
                    'price_id' => $price->id,
                    'name_item' => $item['name_item'],
                    'factor' => $item['factor'], // ThousandthCast: pass decimal values
                ]);
            }
        }
    }
}
