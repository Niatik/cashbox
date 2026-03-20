<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeders must run in this order to satisfy foreign key constraints.
     * BookingSeeder triggers automatic creation of Orders and Payments via events.
     */
    public function run(): void
    {
        $this->call([
            // 1. Permissions and roles first (required for user roles)
            PermissionSeeder::class,
            RoleSeeder::class,

            // 2. Job titles (required for employees)
            JobTitleSeeder::class,

            // 3. Users (required for employees)
            UserSeeder::class,

            // 4. Employees (requires job_titles and users)
            EmployeeSeeder::class,

            // 5. Expense types (required for expenses)
            ExpenseTypeSeeder::class,

            // 6. Social media sources (CRITICAL: "Referral" must be ID 7)
            SocialMediaSeeder::class,

            // 7. Prices with price items (required for bookings/orders)
            PriceSeeder::class,

            // 8. Salary rates (requires job_titles)
            SalaryRateSeeder::class,

            // 9. Rates with rate ratios (requires job_titles)
            RateSeeder::class,

            // 10. Customers (required for bookings)
            CustomerSeeder::class,

            // 11. Bookings (triggers auto-creation of Orders and Payments)
            BookingSeeder::class,

            // 12. Expenses (triggers CashReport updates)
            ExpenseSeeder::class,

            // 13. Salaries (triggers CashReport updates)
            SalarySeeder::class,
        ]);
    }
}
