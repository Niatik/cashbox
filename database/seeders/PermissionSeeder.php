<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'view customers']);
        Permission::create(['name' => 'edit customers']);
        Permission::create(['name' => 'delete customers']);
        Permission::create(['name' => 'create customers']);

        Permission::create(['name' => 'view employees']);
        Permission::create(['name' => 'edit employees']);
        Permission::create(['name' => 'delete employees']);
        Permission::create(['name' => 'create employees']);

        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'edit roles']);
        Permission::create(['name' => 'delete roles']);
        Permission::create(['name' => 'create roles']);

        Permission::create(['name' => 'view permissions']);
        Permission::create(['name' => 'edit permissions']);
        Permission::create(['name' => 'delete permissions']);
        Permission::create(['name' => 'create permissions']);

        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'edit orders']);
        Permission::create(['name' => 'delete orders']);
        Permission::create(['name' => 'create orders']);

        Permission::create(['name' => 'view payments']);
        Permission::create(['name' => 'edit payments']);
        Permission::create(['name' => 'delete payments']);
        Permission::create(['name' => 'create payments']);

        Permission::create(['name' => 'view salaries']);
        Permission::create(['name' => 'edit salaries']);
        Permission::create(['name' => 'delete salaries']);
        Permission::create(['name' => 'create salaries']);

        Permission::create(['name' => 'view expenses']);
        Permission::create(['name' => 'edit expenses']);
        Permission::create(['name' => 'delete expenses']);
        Permission::create(['name' => 'create expenses']);
    }
}
