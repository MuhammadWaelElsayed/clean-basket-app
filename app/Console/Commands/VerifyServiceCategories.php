<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\ItemServiceCategory;

class VerifyServiceCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:verify-categories {item_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify service categories attached to items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $itemId = $this->argument('item_id');

        if ($itemId) {
            $this->verifySingleItem($itemId);
        } else {
            $this->verifyAllItems();
        }
    }

    private function verifySingleItem($itemId)
    {
        $item = Item::with(['serviceCategories', 'services'])->find($itemId);

        if (!$item) {
            $this->error("Item with ID {$itemId} not found!");
            return;
        }

        $this->info("=== Item Details ===");
        $this->info("ID: {$item->id}");
        $this->info("Name: {$item->name}");
        $this->info("Name Arabic: {$item->name_ar}");
        $this->info("Service ID (Main): {$item->service_id}");

        $this->info("\n=== Service Categories ===");
        $categoriesCount = $item->serviceCategories->count();
        $this->info("Total Service Categories: {$categoriesCount}");

        if ($categoriesCount > 0) {
            $this->table(
                ['Category Name', 'Added At'],
                $item->serviceCategories->map(function($category) {
                    return [
                        $category->name,
                        $category->pivot->created_at
                    ];
                })->toArray()
            );
        } else {
            $this->warn("No service categories attached to this item!");
        }

        $this->info("\n=== Available Services ===");
        $servicesCount = $item->services->count();
        $this->info("Total Available Services: {$servicesCount}");

        if ($servicesCount > 0) {
            $this->table(
                ['Service Name', 'Price', 'Discount Price', 'Order Priority ID'],
                $item->services->map(function($service) {
                    return [
                        $service->name,
                        $service->pivot->price,
                        $service->pivot->discount_price ?? 'N/A',
                        $service->pivot->order_priority_id
                    ];
                })->toArray()
            );
        } else {
            $this->warn("No available services attached to this item!");
        }
    }

    private function verifyAllItems()
    {
        $items = Item::with(['serviceCategories', 'services'])->get();

        $this->info("=== All Items Service Categories Summary ===");

        $summary = [];
        foreach ($items as $item) {
            $categoriesCount = $item->serviceCategories->count();
            $categoriesList = $item->serviceCategories->pluck('name')->implode(', ');
            $servicesCount = $item->services->count();
            $servicesList = $item->services->pluck('name')->implode(', ');

            $summary[] = [
                $item->id,
                $item->name,
                $categoriesCount,
                $categoriesList ?: 'No categories',
                $servicesCount,
                $servicesList ?: 'No services'
            ];
        }

        $this->table(
            ['ID', 'Name', 'Categories Count', 'Categories List', 'Services Count', 'Services List'],
            $summary
        );

        // إحصائيات عامة
        $totalItems = $items->count();
        $itemsWithCategories = $items->filter(function($item) {
            return $item->serviceCategories->count() > 0;
        })->count();
        $totalCategoryAttachments = $items->sum(function($item) {
            return $item->serviceCategories->count();
        });

        $this->info("\n=== Statistics ===");
        $this->info("Total Items: {$totalItems}");
        $this->info("Items with Service Categories: {$itemsWithCategories}");
        $this->info("Total Category Attachments: {$totalCategoryAttachments}");

        // التحقق من جدول item_service_categories مباشرة
        $this->info("\n=== Direct Database Check ===");
        $serviceCategories = ItemServiceCategory::with(['item', 'service'])->get();
        $this->info("Total records in item_service_categories: " . $serviceCategories->count());

        foreach ($serviceCategories as $serviceCategory) {
            $this->info("Item {$serviceCategory->item->name} -> Category {$serviceCategory->service->name}");
        }
    }
}
