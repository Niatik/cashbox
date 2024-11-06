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
            $table->unsignedBigInteger('price_id');
            $table->foreign('price_id')
                ->references('id')
                ->on('prices')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedBigInteger('price_item_id');
            $table->foreign('price_item_id')
                ->references('id')
                ->on('price_items')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedBigInteger('social_media_id');
            $table->foreign('social_media_id')
                ->references('id')
                ->on('social_media')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
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
