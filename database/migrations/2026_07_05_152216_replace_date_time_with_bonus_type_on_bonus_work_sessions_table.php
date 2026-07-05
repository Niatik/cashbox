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
        Schema::table('bonus_work_sessions', function (Blueprint $table) {
            $table->dropColumn(['date', 'time']);
            $table->string('bonus_type')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_work_sessions', function (Blueprint $table) {
            $table->dropColumn('bonus_type');
            $table->date('date');
            $table->time('time');
        });
    }
};
