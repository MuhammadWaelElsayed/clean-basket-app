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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('sub_issue_category_id')
                  ->nullable()
                  ->constrained('sub_issue_categories')
                  ->cascadeOnDelete()
                  ->after('issue_category_id')
                  ->comment('التصنيف الفرعي');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['sub_issue_category_id']);
            $table->dropColumn('sub_issue_category_id');
        });
    }
};
