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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('order_code')
                  ->after('sub_issue_category_id')
                  ->comment('رقم الطلب المرتبط بالتذكرة');
            // إذا أردت فهرسًا لتسريع البحث:
            $table->index('order_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['order_code']);
            $table->dropColumn('order_code');
        });
    }
};
