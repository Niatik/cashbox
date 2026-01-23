# AGENTS.md

## Project Overview
This is a Laravel 11 cashbox management system with a Filament 3 admin panel for service booking, payment tracking, and financial reporting. The system manages bookings, orders, payments, expenses, salaries, and generates cash reports.

**Tech Stack:**
- PHP 8.3.29
- Laravel 11 (Framework)
- Filament v3 (Admin Panel)
- Livewire v3
- Pest v3 (Testing)
- Laravel Pint v1 (Formatting)
- SQLite (Development Database)
- Spatie Laravel Permission (RBAC)
- Laravel Sail, Prompts, and MCP

## Foundational Rules
- **Laravel Way:** Use `php artisan make:` commands for migrations, controllers, models, and classes. Always pass `--no-interaction`.
- **Conventions:** Follow existing code structures. Check sibling files for naming and approach.
- **Conciseness:** Be brief in explanations; focus on important details.
- **Documentation:** Only create documentation files if explicitly requested.

## Development Commands
- `php artisan serve` - Start server (Note: Site is also served via Laravel Herd at `https://cashbox.test`).
- `php artisan migrate` - Run database migrations.
- `php artisan db:seed` - Seed the database.
- `php artisan tinker` - Interactive PHP console (use for debugging).
- `vendor/bin/pint --dirty` - Format code (Run before finalizing changes).
- `npm run dev` / `npm run build` - Frontend asset management.

## PHP & Laravel 11 Standards
- **Control Structures:** Always use curly braces.
- **Constructors:** Use PHP 8 constructor property promotion. No empty constructors.
- **Types:** Always use explicit return type declarations and parameter type hints.
- **Comments:** Prefer PHPDoc blocks over inline comments. Define array shapes in PHPDocs.
- **Enums:** Use TitleCase for Enum keys (e.g., `FavoritePerson`).
- **Models:** Use the `casts()` method rather than the `$casts` property.
- **Database:** Use Eloquent relationships with return types. Avoid `DB::`; prefer `Model::query()`. Use eager loading to prevent N+1 issues.
- **Validation:** Use Form Request classes instead of inline validation.
- **Environment:** Use `config()` instead of `env()` outside of configuration files.

## Architecture & Business Logic
The system follows a booking-to-order-to-payment workflow:
1. **Bookings** (`app/Models/Booking.php`) - Service reservations.
2. **Orders** (`app/Models/Order.php`) - Generated from bookings.
3. **Payments/Expenses/Salaries** - Financial records affecting cash flow.
4. **Cash Reports** (`app/Models/CashReport.php`) - Daily summaries handled by `CashReportService.php`.

### Event-Driven Architecture
The system uses model events (`$dispatchesEvents`) for data consistency. Listeners in `app/Listeners/` are auto-discovered.
- Creating/Updating Bookings auto-generates/recreates Orders.
- Financial records (Payments/Expenses/Salaries) trigger balance calculations in Cash Reports.
- Deletions trigger cascade cleanup via listeners.
**Warning:** Never bypass these events using raw queries or mass updates that do not fire events.

### Money Handling
All monetary values use `MoneyCast` to convert between database integers (cents) and application floats. Always use this cast for money-related fields.

## Filament & Livewire
- **Resources:** Located in `app/Filament/Resources/`.
- **Forms/Tables:** Use fluent method chaining. Use `->relationship()` for selects/checkboxes from relationships.
- **Reactivity:** Use `->live()` and `afterStateUpdated(function (Set $set, Get $get) { ... })` for dynamic forms.
- **Artisan:** Use Filament-specific Artisan commands for new components.
- **Livewire:** Requires a single root element. Use `wire:key` in loops. Use `wire:model.live` for real-time updates.

## Tools & Debugging (Junie Boost)
- **Search Docs:** Use the `search-docs` tool for Laravel, Filament, Livewire, and Pest documentation before other approaches. Use broad, simple queries.
- **Artisan:** Use `list-artisan-commands` to verify parameters.
- **URLs:** Use `get-absolute-url` to generate project links.
- **Logs:** Use `browser-logs` to read recent frontend errors.
- **Database:** Use `database-query` for read-only checks or `tinker` for complex debugging.

## Testing Guidelines
- **Framework:** All tests must use Pest. Use `php artisan make:test --pest`.
- **Enforcement:** Every change must be programmatically tested.
- **Coverage:** Test happy paths, failure paths, and edge cases.
- **Filament Testing:** Use `livewire(ResourceClass::class)` or `Livewire::test()`. Ensure authentication is handled (e.g., `Filament::setCurrentPanel('admin')`).
- **Factories:** Use model factories and datasets; do not create models manually in tests.
- **Assertions:** Use specific Pest methods (e.g., `assertForbidden()`) instead of generic status codes.
- **Running Tests:** Filter by file or name: `php artisan test --filter=test_name`.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms
