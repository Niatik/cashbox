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
        Schema::table('work_sessions', function (Blueprint $table) {
            $table->unique(['employee_id', 'date'], 'work_sessions_employee_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_sessions', function (Blueprint $table) {
            $table->dropUnique('work_sessions_employee_date_unique');
        });
    }
};
