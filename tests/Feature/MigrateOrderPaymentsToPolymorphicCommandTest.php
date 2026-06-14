<?php

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

it('migrates order payments to the polymorphic relationship', function () {
    Event::fake();

    $order = Order::factory()->create();

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payable_type' => null,
        'payable_id' => null,
    ]);

    $this->artisan('payments:migrate-to-polymorphic')
        ->assertSuccessful()
        ->expectsOutputToContain('Migrated 1 payment(s).');

    $payment->refresh();

    expect($payment->payable_type)->toBe(Order::class)
        ->and($payment->payable_id)->toBe($order->id)
        ->and($payment->order_id)->toBe($order->id);
});

it('can clear order_id after migrating to the polymorphic relationship', function () {
    Event::fake();

    $order = Order::factory()->create();

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payable_type' => null,
        'payable_id' => null,
    ]);

    $this->artisan('payments:migrate-to-polymorphic --clear-order-id')
        ->assertSuccessful();

    $payment->refresh();

    expect($payment->payable_type)->toBe(Order::class)
        ->and($payment->payable_id)->toBe($order->id)
        ->and($payment->order_id)->toBeNull();
});

it('skips payments that already use the polymorphic relationship', function () {
    Event::fake();

    $order = Order::factory()->create();

    Payment::factory()->create([
        'order_id' => $order->id,
        'payable_type' => Order::class,
        'payable_id' => $order->id,
    ]);

    $this->artisan('payments:migrate-to-polymorphic')
        ->assertSuccessful()
        ->expectsOutput('No payments require migration.');
});

it('supports dry run without updating records', function () {
    Event::fake();

    $order = Order::factory()->create();

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payable_type' => null,
        'payable_id' => null,
    ]);

    $this->artisan('payments:migrate-to-polymorphic --dry-run')
        ->assertSuccessful()
        ->expectsOutputToContain('Would migrate 1 payment(s).');

    $payment->refresh();

    expect($payment->payable_type)->toBeNull()
        ->and($payment->payable_id)->toBeNull();
});

it('skips payments with missing orders', function () {
    Event::fake();

    $order = Order::factory()->create();

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payable_type' => null,
        'payable_id' => null,
    ]);

    Schema::disableForeignKeyConstraints();
    Order::query()->whereKey($order->id)->delete();
    Schema::enableForeignKeyConstraints();

    $this->artisan('payments:migrate-to-polymorphic')
        ->assertFailed()
        ->expectsOutputToContain("Skipping payment #{$payment->id}")
        ->expectsOutputToContain('Skipped 1 payment(s) with missing orders.');
});
