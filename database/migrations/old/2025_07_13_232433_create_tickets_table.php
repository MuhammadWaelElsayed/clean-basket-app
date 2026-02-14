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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();                                                    // رقم التذكرة (رقم تسلسلي داخلي)
            $table->string('ticket_number')->unique()
                  ->comment('رقم التذكرة بصيغة TK-MMDDYYYY####');
            $table->foreignId('issue_category_id')
                  ->constrained('issue_categories')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->dateTime('opened_at')->default(now())
                  ->comment('تاريخ ووقت فتح التذكرة');
            $table->enum('status', ['open','pending','closed'])
                  ->default('open')
                  ->comment('حالة التذكرة');
            $table->text('description')->nullable()
                  ->comment('وصف المشكلة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
