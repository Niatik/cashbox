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
        Schema::create('cash_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->bigInteger('morning_cash_balance')->default(0);
            $table->bigInteger('cash_income')->default(0);
            $table->bigInteger('cashless_income')->default(0);
            $table->bigInteger('cash_expense')->default(0);
            $table->bigInteger('cashless_expense')->default(0);
            $table->bigInteger('cash_salary')->default(0);
            $table->bigInteger('cashless_salary')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_reports');
    }
};
