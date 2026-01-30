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
        Schema::table('rate_ratios', function (Blueprint $table) {
            $table->string('ratio_from')->after('ratio');
            $table->string('ratio_to')->after('ratio_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_ratios', function (Blueprint $table) {
            $table->dropColumn(['ratio_from', 'ratio_to']);
        });
    }
};
