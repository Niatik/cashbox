<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeRole = Role::create(['name' => 'employee']);

        $employeeRole->givePermissionTo('view customers');
        $employeeRole->givePermissionTo('view orders');
        $employeeRole->givePermissionTo('view payments');

        $employeeRole->givePermissionTo('create customers');
        $employeeRole->givePermissionTo('create orders');
        $employeeRole->givePermissionTo('create payments');

        $employeeRole->givePermissionTo('edit customers');
        $employeeRole->givePermissionTo('edit orders');

        $employeeRole->givePermissionTo('delete customers');

        $adminRole = Role::create(['name' => 'admin']);

        $adminRole->givePermissionTo('view employees');
        $adminRole->givePermissionTo('edit employees');
        $adminRole->givePermissionTo('delete employees');
        $adminRole->givePermissionTo('create employees');

        $adminRole->givePermissionTo('view orders');
        $adminRole->givePermissionTo('edit orders');
        $adminRole->givePermissionTo('delete orders');
        $adminRole->givePermissionTo('create orders');

        $adminRole->givePermissionTo('view payments');
        $adminRole->givePermissionTo('edit payments');
        $adminRole->givePermissionTo('delete payments');
        $adminRole->givePermissionTo('create payments');

        $adminRole->givePermissionTo('view salaries');
        $adminRole->givePermissionTo('edit salaries');
        $adminRole->givePermissionTo('delete salaries');
        $adminRole->givePermissionTo('create salaries');

        $adminRole->givePermissionTo('view customers');
        $adminRole->givePermissionTo('edit customers');
        $adminRole->givePermissionTo('delete customers');
        $adminRole->givePermissionTo('create customers');

        $adminRole->givePermissionTo('view expenses');
        $adminRole->givePermissionTo('edit expenses');
        $adminRole->givePermissionTo('delete expenses');
        $adminRole->givePermissionTo('create expenses');

        $adminRole->givePermissionTo('view permissions');
        $adminRole->givePermissionTo('create permissions');
        $adminRole->givePermissionTo('view roles');
        $adminRole->givePermissionTo('edit roles');
        $adminRole->givePermissionTo('delete roles');
        $adminRole->givePermissionTo('create roles');

        Role::create(['name' => 'super-admin']);
    }
}
