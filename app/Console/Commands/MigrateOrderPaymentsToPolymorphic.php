<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class MigrateOrderPaymentsToPolymorphic extends Command
{
    protected $signature = 'payments:migrate-to-polymorphic
                            {--dry-run : Show what would be migrated without updating records}
                            {--clear-order-id : Set order_id to null after migrating to the polymorphic relationship}';

    protected $description = 'Migrate order payments from order_id to the payable polymorphic relationship';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $clearOrderId = (bool) $this->option('clear-order-id');

        $payments = Payment::query()
            ->whereNotNull('order_id')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('payable_type')
                    ->orWhereNull('payable_id');
            })
            ->with('order')
            ->orderBy('id')
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No payments require migration.');

            return self::SUCCESS;
        }

        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($payments as $payment) {
            if ($payment->order === null) {
                $this->warn("Skipping payment #{$payment->id}: order #{$payment->order_id} does not exist.");
                $skippedCount++;

                continue;
            }

            if ($dryRun) {
                $this->line("Would migrate payment #{$payment->id} (order #{$payment->order_id}).");
                $migratedCount++;

                continue;
            }

            Payment::withoutEvents(function () use ($payment, $clearOrderId): void {
                $payment->payable_type = Order::class;
                $payment->payable_id = $payment->order_id;

                if ($clearOrderId) {
                    $payment->order_id = null;
                }

                $payment->save();
            });

            $migratedCount++;
        }

        $action = $dryRun ? 'Would migrate' : 'Migrated';
        $this->info("{$action} {$migratedCount} payment(s).");

        if ($skippedCount > 0) {
            $this->warn("Skipped {$skippedCount} payment(s) with missing orders.");
        }

        return $skippedCount > 0 && $migratedCount === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
