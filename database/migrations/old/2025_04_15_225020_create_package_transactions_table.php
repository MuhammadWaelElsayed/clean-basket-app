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
        Schema::create('package_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']); // نوع العملية: خصم / إضافة
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('related_order_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_transactions');
    }
};
