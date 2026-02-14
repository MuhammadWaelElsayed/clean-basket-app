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
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('distance_km', 10, 2)->nullable()->after('is_picked_up');
            $table->decimal('start_lat', 10, 7)->nullable()->after('distance_km');
            $table->decimal('start_lng', 10, 7)->nullable()->after('start_lat');
            $table->decimal('end_lat', 10, 7)->nullable()->after('start_lng');
            $table->decimal('end_lng', 10, 7)->nullable()->after('end_lat');
            $table->timestamp('started_at')->nullable()->after('end_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'distance_km',
                'start_lat',
                'start_lng',
                'end_lat',
                'end_lng',
                'started_at'
            ]);
        });
    }
};
