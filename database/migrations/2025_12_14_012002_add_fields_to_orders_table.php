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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source_name')->nullable();
            $table->string('source_secret')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('user_id')->change()->nullable();
            $table->foreignId('address_id')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['source_name', 'source_secret', 'address']);
        });
    }
};
