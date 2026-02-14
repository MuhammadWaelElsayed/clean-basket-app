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
            // 1️⃣ إضافة عمود due_amount بعد grand_total
            $table->decimal('due_amount', 10, 2)
                  ->default(0)
                  ->after('grand_total');

            // 2️⃣ تعديل ENUM لحقل pay_status لإضافة حالة "دفع جزئي"
            $table->enum('pay_status', ['Pending', 'دفع_جزئي', 'Paid'])
                  ->default('Pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // إزالة العمود وأرجاع ENUM القديم
            $table->dropColumn('due_amount');

            $table->enum('pay_status', ['Pending', 'Paid'])
                  ->default('Pending')
                  ->change();
        });
    }
};
