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
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->integer('remaining_uses')->default(1);
            $table->date('assigned_at');
            $table->date('expired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('gifted_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('gifted_to_phone', 30)->nullable();
            $table->dateTime('gifted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
    }
};
