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
        Schema::create('b2b_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Gold Tier", "Silver Tier", "Platinum"
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0); // Global discount %
            $table->integer('priority')->default(0); // Higher priority = applied first
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add pricing tier to b2b_clients
        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->foreignId('pricing_tier_id')->nullable()->after('address')
                ->constrained('b2b_pricing_tiers')
                ->nullOnDelete();
        });

        // Custom prices per item per tier
        Schema::create('b2b_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('pricing_tier_id')->constrained('b2b_pricing_tiers')->onDelete('cascade');
            $table->decimal('custom_price', 10, 2); // Override price for this tier
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Or use discount instead
            $table->date('effective_from')->nullable(); // When this price starts
            $table->date('effective_until')->nullable(); // When this price ends
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure one price per item per tier
            $table->unique(['item_id', 'pricing_tier_id'], 'item_tier_unique');
        });

        // Client-specific custom prices (override tier prices)
        Schema::create('b2b_client_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_client_id')->constrained('b2b_clients')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('custom_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable(); // Special agreement notes
            $table->timestamps();

            // Ensure one price per client per item
            $table->unique(['b2b_client_id', 'item_id'], 'client_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_client_item_prices');
        Schema::dropIfExists('b2b_item_prices');

        Schema::table('b2b_clients', function (Blueprint $table) {
            $table->dropForeign(['pricing_tier_id']);
            $table->dropColumn('pricing_tier_id');
        });

        Schema::dropIfExists('b2b_pricing_tiers');
    }
};
