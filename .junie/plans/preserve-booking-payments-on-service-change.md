---
sessionId: session-260920-130119-dqw6
---

# Requirements

### Goal
When a published booking’s service or service variant is replaced, its existing payments must remain attached to the corresponding replacement order. The daily cash report and subsequent cash balances must not decrease or otherwise change merely because the booking was edited.

### In Scope
- Preserve each payment’s `id`, date/time, cash amount, cashless amount, and relation to the replacement `Order`.
- Support replacing either `price_id`, `price_item_id`, or both in a booking line.
- Retain the present transactional order-rebuild workflow and its draft behaviour.

### Out of Scope
- Changing the payment amount to match a newly selected service price.
- Changing payment method from the booking line’s current `is_cash` value.
- Altering the cash-report calculation rules.

# Technical Design

### Current Implementation
`Booking` dispatches `BookingUpdated`, which invokes `app/Listeners/RecreateOrdersWhenBookingUpdated.php`. It snapshots a payment, deletes it, and creates a new one only when the recreated order has the same `price_id` and `price_item_id`. A changed service leaves the snapshot unmatched; `PaymentDeleted` therefore calls `CashReportService::updateOnPaymentDeleted()` without an offsetting creation.

`Payment` is polymorphically related to `Order`; its historical `order_id` column remains nullable and foreign-key constrained. `app/Listeners/DeleteOrderPayments.php` removes only payments that are still attached to the deleted order.

### Proposed Changes
- Refactor `RecreateOrdersWhenBookingUpdated` to retain the existing `Payment` models rather than delete and recreate them during a published-booking rebuild.
- Create the replacement orders first, retaining their booking-line order. Match old orders to new lines by the unchanged line identity where available, then use the non-reorderable repeater’s remaining line order for a service/variant substitution.
- Reassign each matched payment to its replacement order, updating both polymorphic relation fields and legacy `order_id` as applicable. Do not modify `payment_date`, `payment_time`, `payment_cash_amount`, or `payment_cashless_amount`.
- Delete the obsolete orders only after their payments have been reassigned. `DeleteOrderPayments` will then find no payments to remove.
- Keep all work within the existing `DB` transaction, so an exception restores the old order-payment relationships without a partial booking rebuild.

### Rationale
Re-parenting the original payment preserves the financial record itself. Since neither its date nor monetary fields change, the `PaymentUpdated` cash-report handler produces a zero difference, unlike the current delete-without-recreate path.

### Affected Files
- Modify `app/Listeners/RecreateOrdersWhenBookingUpdated.php`.
- Extend `tests/Feature/BookingTest.php`.

# Testing

### Validation
Add focused Pest feature coverage beside the current `restores all payments when booking is updated` scenario.

### Scenarios
- Create a published booking with a payment, change its service and/or variant, and verify the payment still exists, is attached to the recreated order, and retains date, time, cash, and cashless values.
- Capture the day’s `CashReport` income and a later day’s `morning_cash_balance`; after the service change, assert both are unchanged.
- Verify the existing unchanged-service update scenario continues to preserve all payments, then run `php artisan test --compact tests/Feature/BookingTest.php` and format modified PHP with Pint.

# Delivery Steps

### ✓ Step 1: Reassign payments while rebuilding published booking orders
Payments remain attached to the corresponding replacement order when a booking line’s service or variant changes.

- Refactor `app/Listeners/RecreateOrdersWhenBookingUpdated.php` so it builds replacement `Order` records before removing obsolete ones.
- Match existing orders to the rebuilt booking lines, including the changed-service fallback for a retained repeater position.
- Move each existing `Payment` to the matched order while preserving its financial and timestamp fields and keeping `order_id` compatible with its foreign key.
- Delete old orders only after payment reassignment, within the existing database transaction.

### ✓ Step 2: Prove cash-report stability for service replacements
Changing a booking service preserves payment history and leaves cash-report totals unchanged.

- Extend `tests/Feature/BookingTest.php` with a published booking that has a recorded payment and cash-report entries.
- Change the booking line to a different service/variant and assert the original payment is now associated with the replacement order without changes to its amount split or date/time.
- Assert the relevant `cash_income`/`cashless_income` and subsequent `morning_cash_balance` retain their pre-edit values.
- Run the focused booking feature tests and apply Pint to modified PHP files.