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
        Schema::table('order_priorities', function (Blueprint $table) {
            Schema::table('order_priorities', function (Blueprint $table) {
                $table->dropColumn('extra_fee');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_priorities', function (Blueprint $table) {
            $table->decimal('extra_fee', 8, 2)->default(0)->after('name_ar');
        });
    }
};
