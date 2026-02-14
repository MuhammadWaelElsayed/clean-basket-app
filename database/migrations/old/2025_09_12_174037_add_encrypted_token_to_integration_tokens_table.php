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
        Schema::table('integration_tokens', function (Blueprint $table) {
            $table->text('encrypted_token')->nullable()->after('token_hint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_tokens', function (Blueprint $table) {
            $table->dropColumn('encrypted_token');
        });
    }
};
