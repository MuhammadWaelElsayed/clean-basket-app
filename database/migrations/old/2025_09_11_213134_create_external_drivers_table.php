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
        Schema::create('external_drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_driver_id')->unique();
            $table->string('name');
            $table->string('phone', 50)->nullable();
             $table->string('email')->nullable();
            $table->string('provider' , 50)->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_drivers');
    }
};
