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
        Schema::table('item_service_type', function (Blueprint $table) {
            // 1. إضافة العمود مع default = 1 (normal)
            $table->unsignedBigInteger('order_priority_id')
                  ->default(1)
                  ->after('service_type_id');

            // 2. فهرسة العمود
            $table->index('order_priority_id', 'ist_priority_idx');

            // 3. إضافة قيد المفتاح الأجنبي
            $table->foreign('order_priority_id', 'ist_priority_fk')
                  ->references('id')
                  ->on('order_priorities')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('item_service_type', function (Blueprint $table) {
            // أولاً نحذف الـ FK
            $table->dropForeign('ist_priority_fk');
            // ثم نحذف الـ index
            $table->dropIndex('ist_priority_idx');
            // وأخيراً نحذف العمود
            $table->dropColumn('order_priority_id');
        });
    }
};
