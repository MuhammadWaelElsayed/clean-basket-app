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
            $table->enum('type', ['dynamic', 'fixed'])->default('dynamic')->after('id');
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
