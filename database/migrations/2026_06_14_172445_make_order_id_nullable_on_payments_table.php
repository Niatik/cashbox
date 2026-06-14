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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
