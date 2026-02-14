<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();
            // 0=Sunday .. 6=Saturday
            $table->tinyInteger('day_of_week')->comment('0=Sunday .. 6=Saturday');
            $table->enum('day_en', [
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            ]);
            $table->enum('day_ar', [
                'الأحد',
                'الاثنين',
                'الثلاثاء',
                'الأربعاء',
                'الخميس',
                'الجمعة',
                'السبت'
            ]);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false)
                ->comment('1=Closed, 0=Open');
            $table->timestamps();
            $table->unique(['vendor_id', 'day_of_week', 'open_time', 'close_time'], 'uniq_vendor_day_slot');
            $table->index(['vendor_id', 'day_of_week'], 'idx_vendor_day');
        });

        DB::statement("
            ALTER TABLE vendor_working_hours
            ADD CONSTRAINT chk_open_before_close
            CHECK (is_closed = 1 OR (open_time IS NOT NULL AND close_time IS NOT NULL AND open_time < close_time))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_working_hours');
    }
};
