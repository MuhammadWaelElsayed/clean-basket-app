<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings_service_fee', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->decimal('minimum_order_amount', 10, 2)->default(25.00);
            $table->decimal('service_fee_amount', 10, 2)->default(9.00);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // إدراج إعداد افتراض 
        DB::table('settings_service_fee')->insert([
            'is_enabled' => true,
            'minimum_order_amount' => 25.00,
            'service_fee_amount' => 9.00,
            'description' => 'رسوم خدمة للطلبات الصغيرة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_service_fee');
    }
};
