# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based cashbox management system with a Filament admin panel for service booking, payment tracking, and financial reporting. The system manages bookings, orders, payments, expenses, salaries, and generates cash reports.

## Development Commands

### Core Commands
- `php artisan serve` - Start the development server
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed the database with initial data
- `php artisan queue:work` - Process background jobs
- `php artisan tinker` - Interactive PHP console

### Testing
- `php artisan test` - Run all tests using Pest
- `php artisan test --filter=TestName` - Run specific test
- `vendor/bin/pest` - Run Pest tests directly
- `vendor/bin/pest --coverage` - Run tests with coverage

### Code Quality
- `vendor/bin/pint` - Format code using Laravel Pint
- `php artisan model:prune` - Clean up old model records

### Frontend
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Filament Commands
- `php artisan make:filament-resource ModelName` - Create new Filament resource
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
The system uses Laravel events and listeners for automatic calculations:
- `app/Events/` - Domain events (BookingCreated, PaymentCreated, etc.)
- `app/Listeners/` - Event handlers for balance calculations and order management

Key automation:
- When bookings are created/updated, orders are automatically generated
- Payment creation triggers balance recalculation
- Order creation from bookings handles prepayments

### Filament Admin Panel
- **Resources** (`app/Filament/Resources/`) - Admin CRUD interfaces for all models
- **Pages** (`app/Filament/Resources/*/Pages/`) - Custom create/edit/list pages
- **Role-based permissions** using Filament Shield with super-admin and employee roles

### Services
- `app/Services/CashReportService.php` - Handles cash flow calculations and reporting

### Database Design
- Uses SQLite for development (`database/database.sqlite`)
- Money amounts stored as integers (cents) with `MoneyCast` for handling
- Polymorphic relationships between customers, employees, and orders
- Role-based access control using Spatie Laravel Permission

## Testing Setup

Tests use Pest framework with automatic database refresh. Each test runs with a super-admin user created in `tests/Pest.php`. The system uses factories for all models and includes both Feature and Unit tests.

## Key Patterns

### Money Handling
All monetary values use `MoneyCast` to convert between database integers (cents) and display values (dollars). Always use this cast for money fields.

### Role-Based Access
- Super-admins have full access (defined in `AppServiceProvider`)
- Employees have limited access based on permissions
- All Filament resources should implement appropriate policies

### Event Sourcing
Critical business events are tracked through Laravel events. When modifying booking/order/payment logic, ensure appropriate events are dispatched.

### Form Validation
Complex forms use Filament's reactive form system with `Get` and `Set` parameters for dynamic field updates based on user selections.