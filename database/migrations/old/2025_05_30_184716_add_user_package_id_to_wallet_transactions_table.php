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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_package_id')->nullable()->after('wallet_id');

            // إضافة علاقة foreign key
            $table->foreign('user_package_id')
                  ->references('id')
                  ->on('user_packages')
                  ->onDelete('set null'); // في حالة حذف الباقة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_package_id']);
            $table->dropColumn('user_package_id');
        });
    }
};
