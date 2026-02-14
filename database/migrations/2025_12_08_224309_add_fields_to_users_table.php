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
        Schema::table('users', function (Blueprint $table) {
            $table->text('gallery')->nullable();
            $table->boolean('leave_at_the_door')->default(false);
            $table->boolean('hand_over_directly')->default(false);
            $table->boolean('call_upon_arrival')->default(false);
            $table->boolean('dont_call')->default(false);
            $table->boolean('dont_ring_the_doorbell')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
