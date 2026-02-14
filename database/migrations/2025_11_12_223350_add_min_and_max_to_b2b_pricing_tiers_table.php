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
        Schema::table('b2b_pricing_tiers', function (Blueprint $table) {
            $table->integer('min')->default(0)->after('discount_percentage');
            $table->integer('max')->default(0)->after('min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_pricing_tiers', function (Blueprint $table) {
            //
        });
    }
};
