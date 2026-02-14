<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');

            // NEW: Distinguish between pickup and delivery requests
            $table->enum('request_type', ['PICKUP', 'DELIVERY'])->default('PICKUP');

            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'EXPIRED'])->default('PENDING');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'status', 'request_type']);
            $table->index(['order_id', 'status', 'request_type']);
            $table->unique(['order_id', 'driver_id', 'request_type'], 'unique_driver_order_type');
        });

        // Add columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            // Separate drivers for pickup and delivery
            $table->unsignedBigInteger('pickup_driver_id')->nullable()->after('driver_id');
            $table->unsignedBigInteger('delivery_driver_id')->nullable()->after('pickup_driver_id');

            $table->foreign('pickup_driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('delivery_driver_id')->references('id')->on('drivers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pickup_driver_id']);
            $table->dropForeign(['delivery_driver_id']);
            $table->dropColumn(['pickup_driver_id', 'delivery_driver_id']);
        });

        Schema::dropIfExists('driver_requests');
    }
};
