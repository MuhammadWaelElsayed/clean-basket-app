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
        Schema::table('order_items', function (Blueprint $table) {
            // نحذف العمود النصي القديم
            if (Schema::hasColumn('order_items', 'service_type')) {
                $table->dropColumn('service_type');
            }

            // نضيف المفتاح الأجنبي الجديد
            $table->unsignedBigInteger('service_type_id')
                  ->after('item_id');

            $table->foreign('service_type_id')
                  ->references('id')
                  ->on('service_types')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
            // في حال الرجوع نعيد العمود القديم
            $table->string('service_type')->nullable();
        });
    }
};
