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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pickup', 'delivery']); //pickup,delivery
            $table->enum('status', ['new', 'scheduled', 'assigned', 'in-progress', 'completed', 'cancelled', 'rescheduled'])->default('new');
            $table->string('provider')->nullable();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_picked_up')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
