<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemServiceType;

class VerifyServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('=== Verifying Item Services ===');

        $items = Item::with(['service', 'services'])->get();

        $this->command->info("Total Items: " . $items->count());

        $itemsWithServices = 0;
        $totalServiceAttachments = 0;

        foreach ($items as $item) {
            $servicesCount = $item->services->count();
            $servicesList = $item->services->pluck('name')->implode(', ');

            if ($servicesCount > 0) {
                $itemsWithServices++;
                $totalServiceAttachments += $servicesCount;

                $this->command->info("Item ID {$item->id} ({$item->name}): {$servicesCount} services");
                $this->command->info("  Services: {$servicesList}");

                // عرض تفاصيل كل خدمة
                foreach ($item->services as $service) {
                    $this->command->info("    - {$service->name}: Price {$service->pivot->price}, Discount {$service->pivot->discount_price}, Priority {$service->pivot->order_priority_id}");
                }
            } else {
                $this->command->warn("Item ID {$item->id} ({$item->name}): No services attached");
            }
        }

        $this->command->info("\n=== Summary ===");
        $this->command->info("Items with Services: {$itemsWithServices}");
        $this->command->info("Total Service Attachments: {$totalServiceAttachments}");

        // التحقق من جدول item_service_type مباشرة
        $this->command->info("\n=== Direct Database Check ===");
        $serviceTypes = ItemServiceType::with(['item', 'serviceType'])->get();
        $this->command->info("Total records in item_service_type: " . $serviceTypes->count());

        foreach ($serviceTypes as $serviceType) {
            $this->command->info("Item {$serviceType->item->name} -> Service {$serviceType->serviceType->name} (Price: {$serviceType->price})");
        }
    }
}
