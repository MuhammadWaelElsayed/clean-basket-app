<?php

namespace App\Http\Controllers\API\B2b;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderJob;
use App\Models\AddOn;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderPriority;
use App\Models\OrderTracking;
use App\Models\Service;
use App\Models\SettingsServiceFee;
use App\Models\UserAddress;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    /**
     * Get all items with B2B client prices
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $client = $request->user('b2b');

            $client->load('pricingTier');

            if (!$client->pricingTier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pricing tier not found',
                ], 400);
            }
            // Get query parameters
            $perPage = $request->input('per_page', 20);
            $serviceId = $request->input('service_id');
            $search = $request->input('search');

            // Build query
            $query = Item::where('status', true)
                ->with(['service', 'tierPrices' => function ($q) use ($client) {
                    $q->where('pricing_tier_id', $client->pricing_tier_id);
                }]);

            // Filter by service
            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }

            // Search by name
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
            }
            $pricingFilter = $request->input('pricing_filter', 'custom_priced'); // 'all', 'custom_priced', 'default_priced'

            // Filter by pricing status
            if ($pricingFilter === 'custom_priced') {
                // Only items with custom prices for this tier
                $query->whereHas('tierPrices', function ($q) use ($client) {
                    $q->where('pricing_tier_id', $client->pricing_tier_id);
                });
            } elseif ($pricingFilter === 'default_priced') {
                // Only items using default tier pricing (no custom price)
                $query->whereDoesntHave('tierPrices', function ($q) use ($client) {
                    $q->where('pricing_tier_id', $client->pricing_tier_id);
                });
            }

            // Order by importance
            $query->orderBy('importance', 'desc')
                ->orderBy('name', 'asc');

            // Paginate
            $items = $query->paginate($perPage);

            // Transform items with client pricing
            $items->getCollection()->transform(function ($item) use ($client) {
                $originalPrice = (float)$item->price;
                $clientPrice = (float)$client->getPriceForItem($item->id);
                $discount = $originalPrice - $clientPrice;
                $discountPercentage = $originalPrice > 0
                    ? round(($discount / $originalPrice) * 100, 2)
                    : 0;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'description' => $item->description,
                    'description_ar' => $item->description_ar,
                    'image' => $item->image ? url($item->image) : null,
                    'service' => $item->service ? [
                        'id' => $item->service->id,
                        'name' => $item->service->name ?? null,
                        'name_ar' => $item->service->name_ar ?? null,
                    ] : null,
                    'pricing' => [
                        'original_price' => $originalPrice,
                        'your_price' => $clientPrice,
                        'discount_amount' => round($discount, 2),
                        'discount_percentage' => $discountPercentage,
//                        'currency' => 'KWD', // Change as needed
                    ],
                    'importance' => $item->importance,
                    'status' => $item->status,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items->items(),
                    'pagination' => [
                        'total' => $items->total(),
                        'per_page' => $items->perPage(),
                        'current_page' => $items->currentPage(),
                        'last_page' => $items->lastPage(),
                        'from' => $items->firstItem(),
                        'to' => $items->lastItem(),
                    ],
                    'pricing_tier' => $client->pricingTier ? [
                        'id' => $client->pricingTier->id,
                        'name' => $client->pricingTier->name,
                        'name_ar' => $client->pricingTier->name_ar,
                        'discount_percentage' => $client->pricingTier->discount_percentage,
                    ] : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single item with B2B client price
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $client = $request->user('b2b');

            $item = Item::where('status', true)
                ->with('service')
                ->find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $originalPrice = (float)$item->price;
            $clientPrice = (float)$client->getPriceForItem($item->id);
            $discount = $originalPrice - $clientPrice;
            $discountPercentage = $originalPrice > 0
                ? round(($discount / $originalPrice) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'description' => $item->description,
                    'description_ar' => $item->description_ar,
                    'image' => $item->image ? url($item->image) : null,
                    'service' => $item->service ? [
                        'id' => $item->service->id,
                        'name' => $item->service->name ?? null,
                        'name_ar' => $item->service->name_ar ?? null,
                    ] : null,
                    'pricing' => [
                        'original_price' => $originalPrice,
                        'your_price' => $clientPrice,
                        'discount_amount' => round($discount, 2),
                        'discount_percentage' => $discountPercentage,
                        'currency' => 'SAR',
                    ],
                    'importance' => $item->importance,
                    'status' => $item->status,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items by service
     *
     * @param Request $request
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byService(Request $request, $serviceId)
    {
        try {
            $client = $request->user('b2b');

            $service = Service::find($serviceId);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            $items = Item::where('status', true)
                ->where('service_id', $serviceId)
                ->orderBy('importance', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            $itemsWithPricing = $items->map(function ($item) use ($client) {
                $originalPrice = (float)$item->price;
                $clientPrice = (float)$client->getPriceForItem($item->id);
                $discount = $originalPrice - $clientPrice;
                $discountPercentage = $originalPrice > 0
                    ? round(($discount / $originalPrice) * 100, 2)
                    : 0;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'description' => $item->description,
                    'description_ar' => $item->description_ar,
                    'image' => $item->image ? url($item->image) : null,
                    'pricing' => [
                        'original_price' => $originalPrice,
                        'your_price' => $clientPrice,
                        'discount_amount' => round($discount, 2),
                        'discount_percentage' => $discountPercentage,
                        'currency' => 'SAR',
                    ],
                    'importance' => $item->importance,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name ?? null,
                        'name_ar' => $service->name_ar ?? null,
                    ],
                    'items' => $itemsWithPricing,
                    'total_items' => $itemsWithPricing->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all services with item counts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function services(Request $request)
    {
        try {
            $services = Service::withCount(['items' => function ($query) {
                $query->where('status', true);
            }])
                ->having('items_count', '>', 0)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name ?? null,
                        'name_ar' => $service->name_ar ?? null,
                        'items_count' => $service->items_count,
                    ];
                })
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pricing summary for client
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pricingSummary(Request $request)
    {
        try {
            $client = $request->user('b2b');

            $items = Item::where('status', true)->get();

            $totalOriginalPrice = 0;
            $totalClientPrice = 0;
            $itemsWithDiscount = 0;

            foreach ($items as $item) {
                $originalPrice = (float)$item->price;
                $clientPrice = (float)$client->getPriceForItem($item->id);

                $totalOriginalPrice += $originalPrice;
                $totalClientPrice += $clientPrice;

                if ($clientPrice < $originalPrice) {
                    $itemsWithDiscount++;
                }
            }

            $totalDiscount = $totalOriginalPrice - $totalClientPrice;
            $averageDiscountPercentage = $totalOriginalPrice > 0
                ? round(($totalDiscount / $totalOriginalPrice) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'pricing_tier' => $client->pricingTier ? [
                        'id' => $client->pricingTier->id,
                        'name' => $client->pricingTier->name,
                        'name_ar' => $client->pricingTier->name_ar,
                        'tier_discount_percentage' => $client->pricingTier->discount_percentage,
                    ] : null,
//                    'summary' => [
//                        'total_items' => $items->count(),
//                        'items_with_discount' => $itemsWithDiscount,
//                        'total_original_price' => round($totalOriginalPrice, 2),
//                        'total_your_price' => round($totalClientPrice, 2),
//                        'total_savings' => round($totalDiscount, 2),
//                        'average_discount_percentage' => $averageDiscountPercentage,
//                        'currency' => 'SAR',
//                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pricing summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function placeOrderWithItems(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.service_type_id' => 'required|exists:service_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
            'timeslot' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $user = auth('b2b')->user();

        if (blank($user->phone)) {
            return response()->json(['status' => false, 'message' => "No phone found, please contact support", 'data' => []], 400);
        }

        $userId = $user->id;

        $address = UserAddress::where('client_id', $userId)->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => __('No address found for this client')], 422);
        }

//        $vendor = $address->vendor_id;
//
//        if (!$vendor) {
//            return response()->json([
//                'status'  => false,
//                'message' => __('No vendor found in this area'),
//            ], 422);
//        }

        $priority = OrderPriority::first();

        if (!$priority) {
            return response()->json(['status' => false, 'message' => __('Order priority not found')], 422);
        }

        $pickupAt = Carbon::parse($request->timeslot);
        $dropoffAt = $pickupAt->copy()->addDay();

        try {
            DB::beginTransaction();
            $existingDraft = Order::where('user_id', $userId)
                ->where('status', 'DRAFT')
                ->lockForUpdate()
                ->first();

//            if ($existingDraft) {
//                DB::rollBack();
//                return response()->json([
//                    'status'  => false,
//                    'message' => 'You have a draft order, you can\'t create a new order before completing it or deleting it.',
//                    'data'    => $existingDraft,
//                ], 409);
//            }

            $order = Order::create([
                'order_code' => 'CB' . ((Order::latest()->first()->id ?? 0) + 1),
                'type' => 'b2b',
                'user_id' => $userId,
                'address_id' => $address->id,
                'timeslot' => $request->timeslot,
                'pickup_date' => $pickupAt->toDateString(),
                'pickup_time' => $pickupAt->format('H:i'),
                'dropoff_date' => $dropoffAt->toDateString(),
                'dropoff_time' => $dropoffAt->format('H:i'),
                'instructions' => $request->instructions,
                'vendor_id' => $user->vendor_id,
                'driver_id' => $user->driver_id,
                'delivery_fee' => 0,
                'pay_status' => 'Unpaid',
                'sorting' => 'client',
                'status' => 'PLACED',
            ]);

            OrderTracking::firstOrCreate(
                ['order_id' => $order->id, 'status' => 'DRAFT'],
                ['order_id' => $order->id, 'status' => 'DRAFT']
            );

            foreach ($request->items as $itemData) {
                $pivot = DB::table('item_service_type')
                    ->where('item_id', $itemData['item_id'])
                    ->where('service_type_id', $itemData['service_type_id'])
                    ->where('order_priority_id', $request->order_priority_id)
                    ->first(['price', 'discount_price']);

                if (!$pivot) {
                    throw new \Exception("Item service type not found for item_id: {$itemData['item_id']}, service_type_id: {$itemData['service_type_id']}, order_priority_id: {$request->order_priority_id}");
                }

                $unitPrice = $pivot->discount_price !== null
                    ? (float)$pivot->discount_price
                    : (float)$pivot->price;
                $quantity = (int)$itemData['quantity'];
                $baseTotal = $unitPrice * $quantity;

                $addonsTotal = 0;

//                if (! empty($itemData['add_on_ids'])) {
//                    $addOns = AddOn::whereIn('id', $itemData['add_on_ids'])->get();
//                    foreach ($addOns as $addOn) {
//                        $addonsTotal += (float) $addOn->price;
//                    }
//
//                    $quantity = isset($itemData['quantity']) ? (int) $itemData['quantity'] : 1;
//                    $addonsTotal *= $quantity;
//                }

                $totalPrice = $baseTotal + $addonsTotal;

                $orderItem = $order->items()->create([
                    'item_id' => $itemData['item_id'],
                    'service_type_id' => $itemData['service_type_id'],
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);

//                if (! empty($itemData['add_on_ids'])) {
//                    foreach ($itemData['add_on_ids'] as $addOnId) {
//                        $addOn = AddOn::find($addOnId);
//                        if (!$addOn) {
//                            throw new \Exception("Add-on not found for ID: $addOnId");
//                        }
//                        $orderItem->addOns()->attach($addOnId, [
//                            'price' => $addOn->price,
//                        ]);
//                    }
//                }
            }

            $subTotal = $order->items->sum('total_price');

            $deliveryFee = $order->delivery_fee ?? 0;
            $baseAmountBeforeServiceFee = $subTotal + $deliveryFee;
            $vatAmount = round($baseAmountBeforeServiceFee * env('TAX_RATE', 0), 2);

            $serviceFeeData = $this->calculateServiceFee($subTotal, $vatAmount);
            $serviceFee = $serviceFeeData['service_fee'];

            // حساب الإجمالي النهائي
            $baseAmountWithServiceFee = $baseAmountBeforeServiceFee + $serviceFee;
            $grandTotal = round($baseAmountWithServiceFee + $vatAmount, 2);

            $order->update([
                'sub_total' => $subTotal,
                'service_fee' => $serviceFee,
                'service_fee_applied' => $serviceFeeData['service_fee_applied'],
                'vat' => $vatAmount,
                'grand_total' => $grandTotal,
            ]);


            DB::commit();

            $order->load('items.serviceType', 'items.addOns', 'items.item.category');

            return response()->json([
                'status' => true,
                'message' => 'Order created.',
                'data' => array_merge($order->toArray(), [
                    'price' => [
                        'sub_total' => $subTotal,
                        'service_fee' => $serviceFee,
                        'service_fee_applied' => $serviceFeeData['service_fee_applied'],
                        'service_fee_reason' => $serviceFeeData['reason'],
                        'delivery_fee' => $order->delivery_fee,
                        'vat_amount' => $vatAmount,
                        'grand_total' => $grandTotal,
                    ],
                ]),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error("Order creation failed: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
            ], 500);
        }
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'instructions' => 'nullable|string',
            'timeslot' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $is_carpet = $request->input('is_carpet', false);

        $user = auth('b2b')->user();

        if (blank($user->phone)) {
            return response()->json(['status' => false, 'message' => "No phone found, please contact support", 'data' => []], 400);
        }

        $userId = $user->id;
        $isDuplicate = $this->checkDuplicateOrder($userId);
        if ($isDuplicate > 0) {
            return [
                "status" => false,
                "message" => "Order already placed, wait for a moment for next order",
            ];
        }

        $address = UserAddress::where('client_id', $userId)->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => __('No address found for this client')], 422);
        }

        $priority = OrderPriority::first();

        if (!$priority) {
            return response()->json(['status' => false, 'message' => __('Order priority not found')], 422);
        }

        $pickupAt = Carbon::parse($request->timeslot);
        $dropoffAt = $pickupAt->copy()->addDay();

//        $vendorId = $vendor->id ?? 0;
//        $driver = $this->getNearbyDriver($vendorId, $address->lat, $address->lng);

        $order = Order::create([
            "order_code" => 'CB' . ((Order::latest()->first()->id ?? 0) + 1),
            'type' => 'b2b',
            "user_id" => $userId,
            "address_id" => $address->id ?? 0,
            'timeslot' => $request->timeslot,
            'pickup_date' => $pickupAt->toDateString(),
            'pickup_time' => $pickupAt->format('H:i'),
            'dropoff_date' => $dropoffAt->toDateString(),
            'dropoff_time' => $dropoffAt->format('H:i'),
            "instructions" => $request->instructions,
            'vendor_id' => $user->vendor_id,
            'driver_id' => $user->driver_id,
            'sorting' => 'vendor',
            'is_carpet' => $is_carpet,
        ]);
        OrderTracking::firstOrCreate(['order_id' => $order->id, 'status' => "PLACED"], [
            'order_id' => $order->id,
            'status' => "PLACED",
        ]);

        ProcessOrderJob::dispatch($order)->onQueue('default');

        //─── Notifications ─────────────────────────────────
        try {
            $data = [
                "title" => "Hi, " . auth('b2b')->user()->name . " You have placed a new order",
                "title_ar" => "مرحبًا، " . auth('b2b')->user()->name . " لقد قمت بطلب جديد",
                "message" => "Thanks for placing a new order #{$order->order_code}. We will update you soon on it.",
                "message_ar" => "شكرًا لتقديم طلب جديد #{$order->order_code}. سنقوم بتحديثك قريبًا بشأنه.",

                "user" => auth('b2b')->user(),
                "order" => $order,
                // "mail" =>[
                //     "template" => "new_order"
                // ]
            ];
            $this->sendNotifications($data, 'user');

            $data = [
                "title" => "You have a new order for pickup",
                "title_ar" => "لديك طلب جديد للاستلام",
                "message" => "You have a new order for pickup",
                "message_ar" => "لديك طلب جديد للاستلام",
                "order" => $order,
            ];
            $data['user'] = Driver::find($order->driver_id);
            $this->sendNotifications($data, 'driver');

            $data = [
                "title" => "There is a new order #$order->order_code. You can view it once it arrives at your facility.",
                "title_ar" => "هناك طلب جديد #$order->order_code. يمكنك مشاهدته بمجرد وصوله إلى منشأتك.",
                "message" => "There is a new order #$order->order_code. You can view it once it arrives at your facility.",
                "message_ar" => "هناك طلب جديد #$order->order_code.",
                "order" => $order,
            ];
//            $vendor = Vendor::find($vendorId);
//            $data['user'] = $vendor;
//            $this->sendNotifications($data, 'vendor');

            $data = [
                "title" => "New Order is recieved from " . auth('b2b')->user()->name,
                "message" => "A new order #$order->order_code. check its more details.",
                "link" => "admin/order-details/" . $order->id,
            ];
            $this->sendNotifications($data, 'admin');
        } catch (\Exception $ex) {
            // dd($ex->getMessage());
        }
        return response()->json([
            "status" => true,
            'message' => 'Order created.',
            "data" => [
                "order" => $order->toArray(),
            ],
        ], 201);
    }

    public function checkDuplicateOrder($userId)
    {
        $currentTime = Carbon::now()->format('H:i');
        $order = Order::where(['user_id' => $userId])->whereRaw("DATE_FORMAT(created_at, '%H:%i') = ?", [$currentTime])->count();
        return $order;
    }

    public function calculateServiceFee(float $subTotal, float $taxAmount = 0): array
    {
        $settings = SettingsServiceFee::getActiveSettings();

        if (!$settings || !$settings->is_enabled) {
            return [
                'service_fee' => 0,
                'service_fee_applied' => false,
                'reason' => null
            ];
        }

        // حساب المبلغ الإجمالي (المجموع الفرعي + الضريبة)
        $totalAmount = $subTotal + $taxAmount;

        if ($totalAmount < $settings->minimum_order_amount) {
            return [
                'service_fee' => (float)$settings->service_fee_amount,
                'service_fee_applied' => true,
                'reason' => "Order total with tax ({$totalAmount} " . env('CURRENCY', 'SAR') . ") is less than minimum amount ({$settings->minimum_order_amount} " . env('CURRENCY', 'SAR') . ")",
                'minimum_threshold' => (float)$settings->minimum_order_amount,
                'current_subtotal' => (float)$subTotal,
                'current_tax' => (float)$taxAmount,
                'current_total' => (float)$totalAmount
            ];
        }

        return [
            'service_fee' => 0,
            'service_fee_applied' => false,
            'reason' => "Order total with tax ({$totalAmount} " . env('CURRENCY', 'SAR') . ") meets minimum amount ({$settings->minimum_order_amount} " . env('CURRENCY', 'SAR') . ")",
            'minimum_threshold' => (float)$settings->minimum_order_amount,
            'current_subtotal' => (float)$subTotal,
            'current_tax' => (float)$taxAmount,
            'current_total' => (float)$totalAmount
        ];
    }
}
