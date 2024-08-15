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
        Service::factory(10)->create();

        User::factory([
            'name' => 'Nikita',
            'email' => 'nikita.dragunov@gmail.com',
            'password' => '$2y$12$YmvdDfOvnYf9MSfC./DTkeeVLtNCgV0tWQx5cF.OQab1Wjwi4bIjO',
        ])->create();
    }
}
