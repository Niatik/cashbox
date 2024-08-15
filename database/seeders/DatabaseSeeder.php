<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Service::factory(10)->create();

        User::factory([
            'name' => 'Nikita',
            'email' => 'nikita.dragunov@gmail.com',
            'password' => Hash::make('b@stet'),
        ])->create();
    }
}
