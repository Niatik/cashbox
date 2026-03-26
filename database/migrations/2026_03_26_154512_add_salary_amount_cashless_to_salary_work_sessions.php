<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->integer('salary_amount_cashless')->default(0)->after('salary_amount');
        });

        // Migrate data: is_cash=false → salary_amount_cashless = salary_amount, salary_amount = 0
        DB::table('salary_work_sessions')
            ->where('is_cash', false)
            ->update([
                'salary_amount_cashless' => DB::raw('salary_amount'),
                'salary_amount' => 0,
            ]);

        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->dropColumn('is_cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->boolean('is_cash')->default(true)->after('salary_amount');
        });

        // Restore data: salary_amount_cashless > 0 → is_cash = false, salary_amount = salary_amount_cashless
        DB::table('salary_work_sessions')
            ->where('salary_amount_cashless', '>', 0)
            ->update([
                'is_cash' => false,
                'salary_amount' => DB::raw('salary_amount_cashless'),
            ]);

        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->dropColumn('salary_amount_cashless');
        });
    }
};
