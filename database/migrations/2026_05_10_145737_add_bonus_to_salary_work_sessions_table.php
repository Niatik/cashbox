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
        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->integer('bonus')->default(0)->after('salary_amount_cashless');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_work_sessions', function (Blueprint $table) {
            $table->dropColumn('bonus');
        });
    }
};
