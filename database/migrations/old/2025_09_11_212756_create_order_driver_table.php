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
        Schema::create('order_driver', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_ride_id');
            $table->foreignId('order_id')->constrained('orders')->nullable()->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->nullable()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('external_drivers')->nullable()->onDelete('cascade');
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();
            $table->decimal('trip_cost', 10, 2)->nullable();
            $table->string('provider' , 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->dateTimeTz('time_changed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_driver');
    }
};
