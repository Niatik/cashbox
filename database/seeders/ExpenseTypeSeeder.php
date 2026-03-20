<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseTypes = [
            ['name' => 'Owner Payment'],
            ['name' => 'Supplies'],
            ['name' => 'Games'],
            ['name' => 'Advertising'],
            ['name' => 'Rent & Utilities'],
        ];

        foreach ($expenseTypes as $expenseType) {
            ExpenseType::create($expenseType);
        }
    }
}
