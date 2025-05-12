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
        $employeeRole = Role::findOrCreate('employee');

        $employeeRole->givePermissionTo('view bookings');
        $employeeRole->givePermissionTo('view cash reports');
        $employeeRole->givePermissionTo('view customers');
        $employeeRole->givePermissionTo('view expenses');
        $employeeRole->givePermissionTo('view orders');
        $employeeRole->givePermissionTo('view payments');
        $employeeRole->givePermissionTo('view salaries');

        $employeeRole->givePermissionTo('create bookings');
        $employeeRole->givePermissionTo('create cash reports');
        $employeeRole->givePermissionTo('create customers');
        $employeeRole->givePermissionTo('create expenses');
        $employeeRole->givePermissionTo('create orders');
        $employeeRole->givePermissionTo('create payments');
        $employeeRole->givePermissionTo('create salaries');

        $employeeRole->givePermissionTo('edit customers');
        $employeeRole->givePermissionTo('edit orders');

        $employeeRole->givePermissionTo('delete customers');

        $adminRole = Role::findOrCreate('admin');

        $adminRole->givePermissionTo('view bookings');
        $adminRole->givePermissionTo('edit bookings');
        $adminRole->givePermissionTo('delete bookings');
        $adminRole->givePermissionTo('create bookings');

        $adminRole->givePermissionTo('view cash reports');
        $adminRole->givePermissionTo('edit cash reports');
        $adminRole->givePermissionTo('delete cash reports');
        $adminRole->givePermissionTo('create cash reports');

        $adminRole->givePermissionTo('view customers');
        $adminRole->givePermissionTo('edit customers');
        $adminRole->givePermissionTo('delete customers');
        $adminRole->givePermissionTo('create customers');

        $adminRole->givePermissionTo('view employees');
        $adminRole->givePermissionTo('edit employees');
        $adminRole->givePermissionTo('delete employees');
        $adminRole->givePermissionTo('create employees');

        $adminRole->givePermissionTo('view expenses');
        $adminRole->givePermissionTo('edit expenses');
        $adminRole->givePermissionTo('delete expenses');
        $adminRole->givePermissionTo('create expenses');

        $adminRole->givePermissionTo('view expense types');
        $adminRole->givePermissionTo('edit expense types');
        $adminRole->givePermissionTo('delete expense types');
        $adminRole->givePermissionTo('create expense types');

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

        $adminRole->givePermissionTo('view price items');
        $adminRole->givePermissionTo('edit price items');
        $adminRole->givePermissionTo('delete price items');
        $adminRole->givePermissionTo('create price items');

        $adminRole->givePermissionTo('view prices');
        $adminRole->givePermissionTo('edit prices');
        $adminRole->givePermissionTo('delete prices');
        $adminRole->givePermissionTo('create prices');

        $adminRole->givePermissionTo('view salaries');
        $adminRole->givePermissionTo('edit salaries');
        $adminRole->givePermissionTo('delete salaries');
        $adminRole->givePermissionTo('create salaries');

        $adminRole->givePermissionTo('view social media');
        $adminRole->givePermissionTo('edit social media');
        $adminRole->givePermissionTo('delete social media');
        $adminRole->givePermissionTo('create social media');

        $adminRole->givePermissionTo('view users');
        $adminRole->givePermissionTo('edit users');
        $adminRole->givePermissionTo('delete users');
        $adminRole->givePermissionTo('create users');

        $adminRole->givePermissionTo('view permissions');

        $adminRole->givePermissionTo('view roles');
        $adminRole->givePermissionTo('edit roles');
        $adminRole->givePermissionTo('delete roles');
        $adminRole->givePermissionTo('create roles');

        Role::findOrCreate('super-admin');
    }
}
