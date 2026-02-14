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
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable(); // maysar, wallet, package
            $table->string('transaction_id')->nullable(); // transaction id
            $table->string('payment_reference')->nullable(); // payment reference
            $table->enum('status', ['paid', 'refunded', 'failed'])->default('paid');
            $table->string('refund_reference')->nullable(); // refund reference
            $table->json('refund_response')->nullable(); // refund response
            $table->text('refund_notes')->nullable(); // refund notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'amount', 'vat_amount', 'payment_method',
                'transaction_id', 'payment_reference', 'status',
                'refund_reference', 'refund_response', 'refund_notes'
            ]);
        });
    }
};
