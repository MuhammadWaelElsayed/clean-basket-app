<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']); // نوع العملية: إضافة أو خصم
            $table->decimal('amount', 10, 2);
            $table->string('source')->nullable(); // مصدر العملية: admin, user, system
            $table->string('description')->nullable(); // وصف للعملية
            $table->unsignedBigInteger('related_order_id')->nullable(); // إذا كان الخصم له علاقة بطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
