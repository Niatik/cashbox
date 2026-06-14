<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('product_orders', 'options')) {
                $table->json('options')->nullable();
            }

            if (! Schema::hasColumn('product_orders', 'employee_id')) {
                $table->foreignId('employee_id')
                    ->nullable()
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_orders', function (Blueprint $table) {
            if (Schema::hasColumn('product_orders', 'employee_id')) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            }

            if (Schema::hasColumn('product_orders', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};
