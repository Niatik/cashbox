# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 cashbox management system with a Filament 3 admin panel for service booking, payment tracking, and financial reporting. The system manages bookings, orders, payments, expenses, salaries, and generates cash reports.

**Tech Stack:**
- Laravel 11 (PHP 8.2+)
- Filament 3 (admin panel)
- Pest 3 (testing framework)
- Laravel Pint (code formatting)
- SQLite (development database)
- Spatie Laravel Permission (role-based access control)

## Development Commands

### Core Commands
- `php artisan serve` - Start the development server (though Laravel Herd auto-serves at https://cashbox.test)
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed the database with initial data
- `php artisan tinker` - Interactive PHP console

### Testing
- `php artisan test` - Run all tests using Pest
- `php artisan test tests/Feature/BookingTest.php` - Run specific test file
- `php artisan test --filter=test_name` - Run specific test by name
- `vendor/bin/pint --dirty` - Format code (run before finalizing changes)

### Frontend
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Filament Commands
- `php artisan make:filament-resource ModelName --generate` - Create new Filament resource with form/table
- `php artisan make:filament-page PageName` - Create new Filament page
- `php artisan filament:upgrade` - Upgrade Filament components

## Architecture

### Core Business Logic
The system revolves around a booking-to-order-to-payment workflow:

1. **Bookings** (`app/Models/Booking.php`) - Customer service reservations with dates, times, and prepayments
2. **Orders** (`app/Models/Order.php`) - Individual service items generated from bookings
3. **Payments** (`app/Models/Payment.php`) - Payment records for orders (cash/cashless)
4. **Expenses** (`app/Models/Expense.php`) - Business expenses (cash/cashless)
5. **Salaries** (`app/Models/Salary.php`) - Employee salaries (cash/cashless)
6. **Cash Reports** (`app/Models/CashReport.php`) - Daily financial summaries

### Event-Driven Architecture
The system uses Laravel model events dispatched directly from models via `$dispatchesEvents` property. Events auto-discover their listeners (no EventServiceProvider needed in Laravel 11).

**Key Event Flow:**
- `BookingCreated` → `CreateOrdersWhenBookingCreated` - Auto-generates orders from booking price items
- `BookingUpdated` → `RecreateOrdersWhenBookingUpdated` - Recreates orders when booking changes
- `BookingDeleting` → `DeleteBookingOrders` - Cleans up related orders
- `OrderCreated` → `CreatePaymentForOrderPrepayment` - Creates prepayment record if booking had prepayment
- `OrderDeleting` → `DeleteOrderPayments` - Cleans up related payments
- `PaymentCreated/Updated/Deleted` → `CalculateBalanceOnPayment*` - Updates cash reports
- `ExpenseCreated/Updated/Deleted` → `CalculateBalanceOnExpense*` - Updates cash reports
- `SalaryCreated/Updated/Deleted` → `CalculateBalanceOnSalary*` - Updates cash reports

**Critical Pattern:** Models dispatch events using `$dispatchesEvents` property. Listeners are auto-discovered from `app/Listeners/` by Laravel's event discovery. When modifying booking/order/payment logic, ensure you understand which events will fire.

### Filament Admin Panel
- **Resources** (`app/Filament/Resources/`) - Admin CRUD interfaces for all models
- **Pages** (`app/Filament/Resources/*/Pages/`) - Custom create/edit/list pages
- **Role-based permissions** using Filament Shield with super-admin and employee roles
- **Important:** Use `->relationship()` method on form components when populating selects/checkboxes from relationships

### Services
- `app/Services/CashReportService.php` - Handles cash flow calculations and daily cash report regeneration. Contains methods like `calculateAndSaveDailyData()` to rebuild all cash reports from scratch.

### Database Design
- Uses SQLite for development (`database/database.sqlite`)
- Money amounts stored as integers (cents) in database, converted via `MoneyCast`
- Models use `casts()` method (Laravel 11 pattern) rather than `$casts` property
- Role-based access control using Spatie Laravel Permission

## Testing Setup

Tests use Pest 3 with automatic database refresh. Setup in `tests/Pest.php`:
- Auto-creates `super-admin` and `employee` roles before each test
- Creates authenticated super-admin user for all tests
- Uses `RefreshDatabase` trait

**Writing Tests:**
- Use `php artisan make:test --pest TestName` for feature tests
- Add `--unit` flag for unit tests
- All tests must test happy paths, failure paths, and edge cases
- Use model factories (never create models directly in tests)
- For Filament tests, use `livewire(ResourceClass::class)` assertions

## Key Patterns

### Money Handling
All monetary values use `MoneyCast` to convert between database integers (cents) and application floats (dollars). The cast multiplies by 100 when storing and divides by 100 when retrieving. Always use this cast for any money-related fields.

### Role-Based Access
- Super-admins have full access (defined via `Gate::before()` in `AppServiceProvider`)
- Employees have limited access based on permissions
- All Filament resources should implement appropriate policies

### Event-Driven Side Effects
**Critical:** The system relies heavily on model events for maintaining data consistency. When:
- Creating/updating bookings → Orders are auto-generated/recreated
- Creating/updating/deleting payments/expenses/salaries → Cash reports are auto-updated
- Deleting bookings/orders → Related records are cascade-deleted via listeners

Never bypass these events by using raw queries or mass updates without firing events.

### Filament Forms
Forms use Filament's reactive system with `Get` and `Set` closures:
- Use `->live()` on fields that should trigger reactive updates
- Use `afterStateUpdated()` with `Set` to update other form fields
- Complex forms in `BookingResource` and `OrderResource` demonstrate this pattern

### Laravel 11 Structure
- No `app/Http/Middleware/` - middleware registered in `bootstrap/app.php`
- No `app/Console/Kernel.php` - commands auto-register from `app/Console/Commands/`
- Service providers in `bootstrap/providers.php`
- Events auto-discover listeners (no manual registration needed)