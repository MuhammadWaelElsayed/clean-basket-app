<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\AddOn;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * POST /api/orders/{order}/items
     * إنشاء بند طلب جديد مع حساب السعر والإضافات
     */
    public function store(Request $request, Order $order)
    {
        // 1. التحقق من المدخلات
        $data = $request->validate([
            'item_id'         => 'required|exists:items,id',
            'service_type_id' => 'required|exists:service_types,id',
            'quantity'        => 'required|integer|min:1',
            'add_on_ids'      => 'array',
            'add_on_ids.*'    => 'exists:add_ons,id',
        ]);

        // 2. جلب أولويّة الطلب الحالية
        $priorityId = $order->order_priority_id;

        // 3. جلب السعر من pivot بناءً على الثلاثية (item, service_type, order_priority)
        $pivot = DB::table('item_service_type')
            ->where('item_id', $data['item_id'])
            ->where('service_type_id', $data['service_type_id'])
            ->where('order_priority_id', $priorityId)   // ← هنا أضفنا الفلتر
            ->first(['price', 'discount_price']);

        $unitPrice = $pivot->discount_price !== null
                     ? (float)$pivot->discount_price
                     : (float)$pivot->price;
        $quantity  = $data['quantity'];
        $totalPrice = $unitPrice * $quantity;

        // 4. إنشاء بند الطلب
        $orderItem = OrderItem::create([
            'order_id'        => $order->id,
            'item_id'         => $data['item_id'],
            'service_type_id' => $data['service_type_id'],
            'price'           => $unitPrice,
            'quantity'        => $quantity,
            'total_price'     => $totalPrice,
        ]);

        // 5. ربط الإضافات وتعديل السعر الإجمالي
        if (! empty($data['add_on_ids'])) {
            foreach ($data['add_on_ids'] as $addOnId) {
                $addOn = AddOn::findOrFail($addOnId);
                $orderItem->addOns()->attach($addOnId, ['price' => $addOn->price]);
                $totalPrice += (float)$addOn->price;
            }
            $orderItem->update(['total_price' => $totalPrice]);
        }

        // 6. إعادة تحميل العلاقات و الإرجاع
        $orderItem->load('serviceType', 'addOns');

        return response()->json([
            'data' => $orderItem
        ], 201);
    }
}
