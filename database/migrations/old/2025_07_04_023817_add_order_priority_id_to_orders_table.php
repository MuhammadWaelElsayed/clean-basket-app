<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // نضيف الحقل مع قيمة افتراضية 1 (يفترض أن تكون 'عادي')
            $table->unsignedBigInteger('order_priority_id')
                  ->default(1)
                  ->after('user_id');

            // رابط المفتاح الأجنبي
            $table->foreign('order_priority_id')
                  ->references('id')
                  ->on('order_priorities')
                  ->onDelete('restrict');
        });

        // نحدّث كل السجلات الحالية لتعطيها القيمة الافتراضية
        DB::table('orders')->update(['order_priority_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_priority_id']);
            $table->dropColumn('order_priority_id');
        });
    }
};
