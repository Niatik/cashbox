<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'nikita.dragunov@gmail.com')->first();

        if ($user) {
            $user->assignRole('super-admin');
        }

        $user = User::where('email', 'emp@example.com')->first();
        if ($user) {
            $user->assignRole('employee');
        }
    }
}
