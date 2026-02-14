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
        Schema::create('order_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "Normal", "Urgent"
            $table->string('name_ar');        // e.g. "عادي", "مستعجل"
            $table->unsignedInteger('lead_time')->default(24)->comment('ساعات التأخير المسموح بها لاستلام الطلب');
            $table->decimal('extra_fee', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_priorities');
    }
};
