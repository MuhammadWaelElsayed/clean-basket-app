<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixItemServiceCategoriesTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:item-service-categories-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the item_service_categories table structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Starting to fix item_service_categories table...');

        try {
            // التحقق من وجود الجدول
            if (Schema::hasTable('item_service_categories')) {
                $this->info('📋 Table exists, checking structure...');

                // حذف الجدول الحالي
                Schema::dropIfExists('item_service_categories');
                $this->info('🗑️  Dropped existing table');
            }

            // إنشاء الجدول من جديد
            Schema::create('item_service_categories', function ($table) {
                $table->id(); // هذا سيضمن أن id هو AUTO_INCREMENT
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('service_id');
                $table->timestamps();

                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
                $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

                // منع تكرار نفس الخدمة لنفس العنصر
                $table->unique(['item_id', 'service_id']);
            });

            $this->info('✅ Table created successfully with proper structure');

            // التحقق من البنية
            $columns = DB::select("DESCRIBE item_service_categories");
            $this->info('📊 Table structure:');

            foreach ($columns as $column) {
                $this->line("  - {$column->Field}: {$column->Type} " .
                           ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') .
                           ($column->Extra ? " ({$column->Extra})" : ''));
            }

            $this->info('🎉 Table fixed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Error fixing table: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
