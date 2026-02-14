<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\ItemServiceType;

class VerifyItemServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:verify-services {item_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify services attached to items';

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
        $item = Item::with(['services', 'serviceCategories'])->find($itemId);

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
        $serviceCategoriesCount = $item->serviceCategories->count();
        $this->info("Total Service Categories: {$serviceCategoriesCount}");
        if ($serviceCategoriesCount > 0) {
            $categoriesList = $item->serviceCategories->pluck('name')->implode(', ');
            $this->info("Service Categories: {$categoriesList}");
        }

        $this->info("\n=== Attached Services ===");
        $servicesCount = $item->services->count();
        $this->info("Total Services: {$servicesCount}");

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
            $this->warn("No services attached to this item!");
        }
    }

    private function verifyAllItems()
    {
        $items = Item::with(['services', 'serviceCategories'])->get();

        $this->info("=== All Items Services Summary ===");

        $summary = [];
        foreach ($items as $item) {
            $servicesCount = $item->services->count();
            $servicesList = $item->services->pluck('name')->implode(', ');
            $categoriesCount = $item->serviceCategories->count();
            $categoriesList = $item->serviceCategories->pluck('name')->implode(', ');

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
        $itemsWithServices = $items->filter(function($item) {
            return $item->services->count() > 0;
        })->count();
        $totalServices = $items->sum(function($item) {
            return $item->services->count();
        });

        $this->info("\n=== Statistics ===");
        $this->info("Total Items: {$totalItems}");
        $this->info("Items with Services: {$itemsWithServices}");
        $this->info("Total Service Attachments: {$totalServices}");
    }
}
