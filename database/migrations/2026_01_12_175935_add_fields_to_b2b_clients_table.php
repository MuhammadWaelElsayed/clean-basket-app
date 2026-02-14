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
            $table->decimal('service_fees', 8, 2)->nullable()->after('address');
            $table->decimal('delivery_fees', 8, 2)->nullable()->after('service_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropColumn(['service_fees', 'delivery_fees']);
        });
    }
};
