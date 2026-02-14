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
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('is_active')->constrained('vendors')->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->after('vendor_id')->constrained('drivers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['driver_id']);
            $table->dropColumn(['vendor_id', 'driver_id']);
        });
    }
};
