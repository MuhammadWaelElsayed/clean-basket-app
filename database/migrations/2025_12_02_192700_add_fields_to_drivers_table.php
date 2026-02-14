<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->enum('role', ['FREELANCE', 'FULL_TIME', 'THIRD_PARTY'])->default('FREELANCE');
            $table->enum('vehicle_type', ['MOTORCYCLE', 'CAR', 'VAN', 'TRUCK'])->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->json('device_info')->nullable();
            $table->string('license')->nullable();
            $table->string('id_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['role', 'vehicle_type', 'vehicle_plate', 'device_info', 'license', 'id_image']);
        });
    }
};
