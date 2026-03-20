<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates daily expenses across the 30-day historical period.
     * expense_amount uses MoneyCast - pass dollar amounts.
     */
    public function run(): void
    {
        $expenseTypes = ExpenseType::all();

        $descriptions = [
            'Owner Payment' => ['Weekly owner payment', 'Monthly owner withdrawal', 'Owner distribution'],
            'Supplies' => ['Cleaning supplies', 'Office supplies', 'Paper towels', 'Hand sanitizer'],
            'Games' => ['New board game', 'Card deck replacement', 'Game equipment repair'],
            'Advertising' => ['Facebook ads', 'Instagram promotion', 'Flyers printing', 'Local magazine ad'],
            'Rent & Utilities' => ['Monthly rent', 'Electricity bill', 'Water bill', 'Internet service'],
        ];

        // Create expenses for the past 30 days
        for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();

            // 1-3 expenses per day
            $expensesPerDay = rand(1, 3);

            for ($i = 0; $i < $expensesPerDay; $i++) {
                $expenseType = $expenseTypes->random();
                $typeDescriptions = $descriptions[$expenseType->name] ?? ['General expense'];
                $description = $typeDescriptions[array_rand($typeDescriptions)];

                // Amount varies by expense type
                $amount = match ($expenseType->name) {
                    'Owner Payment' => rand(200, 500),
                    'Supplies' => rand(20, 100),
                    'Games' => rand(30, 150),
                    'Advertising' => rand(50, 200),
                    'Rent & Utilities' => rand(100, 400),
                    default => rand(20, 100),
                };

                // Add some cents variation
                $amount = $amount + (rand(0, 99) / 100);

                Expense::create([
                    'expense_date' => $date,
                    'expense_type_id' => $expenseType->id,
                    'description' => $description,
                    'expense_amount' => $amount, // MoneyCast: dollar amount
                    'is_cash' => (bool) rand(0, 1),
                ]);
            }
        }
    }
}
