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
        Schema::create('sub_issue_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم التصنيف الفرعي');
            $table->foreignId('issue_category_id')
                  ->constrained('issue_categories')
                  ->cascadeOnDelete()
                  ->comment('المرجع للتصنيف الرئيسي');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_issue_categories');
    }
};
