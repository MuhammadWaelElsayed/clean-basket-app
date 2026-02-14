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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_webhook_id')->constrained('partner_webhooks')->onDelete('cascade');
            $table->string('event')->nullable();
            $table->json('payload');
            $table->enum('status', ['success', 'failed', 'permanently_failed'])->default('failed');
            $table->integer('status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('duration_ms')->nullable()->comment('Response time in milliseconds');
            $table->text('error_message')->nullable();
            $table->integer('attempt')->default(1);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('partner_webhook_id');
            $table->index('status');
            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
