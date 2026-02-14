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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->decimal('price', 10, 2);           // رسوم الاشتراك
            $table->decimal('cashback_amount', 10, 2); // قيمة الكاش باك
            $table->decimal('delivery_fee', 10, 2);    // رسوم التوصيل (0 لو مجاني)
            $table->integer('duration_days')->nullable();         // مدة الباقة بالأيام
            $table->boolean('has_priority')->default(false); // أولوية في الخدمة
            $table->integer('voucher_count')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
