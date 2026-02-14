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
        Schema::table('external_drivers', function (Blueprint $table) {
            // تغيير نوع العمود من unsignedBigInteger إلى string
            $table->string('external_driver_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_drivers', function (Blueprint $table) {
            // إرجاع النوع إلى unsignedBigInteger
            $table->unsignedBigInteger('external_driver_id')->change();
        });
    }
};
