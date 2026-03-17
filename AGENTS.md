# AGENTS.md

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

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 3 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, architecture testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v11 rules ===

# Laravel 11

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Laravel 11 brought a new streamlined file structure which this project now uses.

## Laravel 11 Structure

- In Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- No app\Console\Kernel.php - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Commands auto-register - files in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

## New Artisan Commands

- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== filament/filament rules ===

## Filament

- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan

- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features

- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships

- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>

## Testing

- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>

## Version 3 Changes To Focus On

- Resources are located in `app/Filament/Resources/` directory.
- Resource pages (List, Create, Edit) are auto-generated within the resource's directory - e.g., `app/Filament/Resources/PostResource/Pages/`.
- Forms use the `Forms\Components` namespace for form fields.
- Tables use the `Tables\Columns` namespace for table columns.
- A new `Filament\Forms\Components\RichEditor` component is available.
- Form and table schemas now use fluent method chaining.
- Added `php artisan filament:optimize` command for production optimization.
- Requires implementing `FilamentUser` contract for production access control.
