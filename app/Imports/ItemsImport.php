<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\OrderPriority;
use App\Models\ItemServiceType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // ✅ أنشئ العنصر في جدول items
            $item = Item::create([
                'name'           => $row['name'],
                'name_ar'        => $row['name_ar'],
                'description'    => $row['description'],
                'description_ar' => $row['description_ar'],
                'price'          => $row['price'],
                'service_id'     => $row['services'], // service_id
            ]);

            // ✅ فك نوع الخدمات و الأسعار المرتبطة بها
            $serviceTypeIds = explode(',', $row['service_types']);
            $servicePrices = explode(',', $row['service_prices']);
            $serviceDiscounts = explode(',', $row['service_discount_prices']);
            $priorityId = $row['order_priority_id'];

            foreach ($serviceTypeIds as $index => $serviceTypeId) {
                ItemServiceType::create([
                    'item_id'          => $item->id,
                    'service_type_id'  => $serviceTypeId,
                    'order_priority_id'=> $priorityId,
                    'price'            => $servicePrices[$index] ?? 0,
                    'discount_price'   => $serviceDiscounts[$index] ?? null,
                ]);
            }
        }
    }
}

