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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('order_date');
            $table->time('order_time');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedBigInteger('social_media_id');
            $table->foreign('social_media_id')
                ->references('id')
                ->on('social_media')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->integer('time_order');
            $table->integer('people_number');
            $table->bigInteger('sum')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
