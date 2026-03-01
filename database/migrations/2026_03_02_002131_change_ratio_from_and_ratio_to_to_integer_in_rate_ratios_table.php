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
            $table->integer('ratio_from')->change();
            $table->integer('ratio_to')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_ratios', function (Blueprint $table) {
            $table->string('ratio_from')->change();
            $table->string('ratio_to')->change();
        });
    }
};
