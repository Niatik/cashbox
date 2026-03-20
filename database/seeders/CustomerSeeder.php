<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            ['name' => 'James Anderson', 'phone' => '+1 (555) 101-0001'],
            ['name' => 'Emma Thompson', 'phone' => '+1 (555) 101-0002'],
            ['name' => 'William Johnson', 'phone' => '+1 (555) 101-0003'],
            ['name' => 'Olivia Martinez', 'phone' => '+1 (555) 101-0004'],
            ['name' => 'Benjamin Lee', 'phone' => '+1 (555) 101-0005'],
            ['name' => 'Sophia Garcia', 'phone' => '+1 (555) 101-0006'],
            ['name' => 'Lucas Miller', 'phone' => '+1 (555) 101-0007'],
            ['name' => 'Isabella White', 'phone' => '+1 (555) 101-0008'],
            ['name' => 'Henry Taylor', 'phone' => '+1 (555) 101-0009'],
            ['name' => 'Mia Harris', 'phone' => '+1 (555) 101-0010'],
            ['name' => 'Alexander Clark', 'phone' => '+1 (555) 101-0011'],
            ['name' => 'Charlotte Lewis', 'phone' => '+1 (555) 101-0012'],
            ['name' => 'Daniel Walker', 'phone' => '+1 (555) 101-0013'],
            ['name' => 'Amelia Robinson', 'phone' => '+1 (555) 101-0014'],
            ['name' => 'Matthew Young', 'phone' => '+1 (555) 101-0015'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
