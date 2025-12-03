<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('reward_tier_rates', function (Blueprint $table) {
           $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedBigInteger('reward_id');
            $table->unsignedBigInteger('tier_id');
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('tiers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_tier_rates');
    }
};
