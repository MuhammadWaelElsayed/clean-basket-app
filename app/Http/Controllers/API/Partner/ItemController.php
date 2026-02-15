<?php

namespace App\Http\Controllers\API\Partner;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ValidatePartner;
use App\Jobs\ProcessOrderJob;
use App\Models\AddOn;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderPriority;
use App\Models\OrderTracking;
use App\Models\PromoCode;
use App\Models\Service;
use App\Models\SettingsServiceFee;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPromoCode;
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

            // Get query parameters
            $perPage = $request->input('per_page', 20);
            $serviceId = $request->input('service_id');
            $search = $request->input('search');

            // Build query
            $query = Item::with(['category', 'services'])->where('status', true);

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

            // Order by importance
            $query->orderBy('importance', 'desc')
                ->orderBy('name', 'asc');

            // Paginate
            $items = $query->paginate($perPage);

            // Transform items with client pricing
            $items->getCollection()->transform(function ($item) {
                $originalPrice = (float)$item->price;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'description' => $item->description,
                    'description_ar' => $item->description_ar,
                    'image' => $item->image ? url($item->image) : null,
                    'price' => $originalPrice,
                    'importance' => $item->importance,
                    'status' => $item->status,
                    'category' => $item->category,
                    'services' => $item->services,
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
            $item = Item::where('status', true)
                ->with(['category', 'services'])
                ->find($id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $originalPrice = (float)$item->price;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'name_ar' => $item->name_ar,
                    'description' => $item->description,
                    'description_ar' => $item->description_ar,
                    'image' => $item->image ? url($item->image) : null,
                    'price' => $originalPrice,
                    'importance' => $item->importance,
                    'status' => $item->status,
                    'category' => $item->category,
                    'services' => $item->services,
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


    public function placeOrderWithItems(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.service_type_id' => 'required|exists:service_types,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.add_on_ids' => 'nullable|array',
            'items.*.add_on_ids.*' => 'required|numeric|exists:add_ons,id',
            'instructions' => 'nullable|string',
            'pickup_date' => 'required|date_format:Y-m-d',
            "pickup_time" => "required",
            'dropoff_date' => 'required|date_format:Y-m-d',
            'dropoff_time' => 'required',
            'full_name' => 'required|string|max:255',
            'address' => 'required|max:255',
            'phone' => 'required|string|max:255|regex:/^966\d{7,10}$/',
            'payment_response' => 'required|array',
            'promo_code' => 'nullable|string'
        ], [
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف غير صالح. يجب أن يبدأ بـ 966 ويكون مكونًا من 7 إلى 10 أرقام.',
            'phone.unique' => 'رقم الهاتف موجود بالفعل.',
        ]);


        $source = ValidatePartner::getSource($request->header('X-Source'));

        $lon = $request->header('X-Lon');
        $lat = $request->header('X-Lat');

        try {
            DB::beginTransaction();
            $existingDraft = Order::where('source_secret', $source['secret'])
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

            $vendor = $this->getAreaVendor($lat, $lon);

            if (!$vendor) {
                return response()->json([
                    'status' => false,
                    "message" => __('api')['order_vendor'],
                    'data' => [],
                ], 400);
            }

            $user = User::firstOrCreate(['phone' => $request->phone], [
                'phone' => $request->phone,
                'email' => $request->phone . '@partner.com',
                'first_name' => $request->full_name,
                'source_name' => $source['name'],
                'source_secret' => $source['secret'],
                'lat' => $lat,
                'lng' => $lon,
                'status' => 1,
                'app_lang' => 'en',
            ]);

            if ($userToken = $request->header('X-User-Token')) {
                $meta = $user->meta;
                $meta['user_token'] = $userToken;
                $user->update(['meta' => $meta]);
            }

            $address = UserAddress::firstOrCreate(['user_id', $user->id], [
                'user_id' => $user->id,
                'lat' => $lat,
                'lng' => $lon,
                'vendor_id' => $vendor->id,
                'appartment' => $request->address,
            ]);

            $order = Order::create([
                'order_code' => 'CB' . ((Order::latest()->first()->id ?? 0) + 1),
                'type' => 'partner',
                'address_id' => $address->id,
                'user_id' => $user->id,
                'source_name' => $source['name'],
                'source_secret' => $source['secret'],
                'address' => $request->address,
                'timeslot' => $request->timeslot,
                'phone' => $request->phone,
                "pickup_date" => $request->pickup_date,
                "pickup_time" => $request->pickup_time,
                "dropoff_date" => $request->dropoff_date,
                "dropoff_time" => $request->dropoff_time,
                'instructions' => $request->instructions,
                'vendor_id' => $vendor->id,
                'driver_id' => null,
                'delivery_fee' => 0,
                'pay_status' => 'Paid',
                'sorting' => 'client',
                'status' => 'PLACED',
                'payment_response' => filled($request->payment_response) ? json_encode($request->payment_response) : null,
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

                if (!empty($itemData['add_on_ids'])) {
                    $addOns = AddOn::whereIn('id', $itemData['add_on_ids'])->get();
                    foreach ($addOns as $addOn) {
                        $addonsTotal += (float)$addOn->price;
                    }

                    $quantity = isset($itemData['quantity']) ? (int)$itemData['quantity'] : 1;
                    $addonsTotal *= $quantity;
                }

                $totalPrice = $baseTotal + $addonsTotal;

                $orderItem = $order->items()->create([
                    'item_id' => $itemData['item_id'],
                    'service_type_id' => $itemData['service_type_id'],
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);

                if (!empty($itemData['add_on_ids'])) {
                    foreach ($itemData['add_on_ids'] as $addOnId) {
                        $addOn = AddOn::find($addOnId);
                        if (!$addOn) {
                            throw new \Exception("Add-on not found for ID: $addOnId");
                        }
                        $orderItem->addOns()->attach($addOnId, [
                            'price' => $addOn->price,
                        ]);
                    }
                }
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

            $promoCode = $request->promo_code ?? null;
            if ($promoCode) {
                $promoDiscount = $this->applyPromoCode($promoCode, $baseAmountBeforeServiceFee);
                if ($promoDiscount) {
                    $order->updateQuietly([
                        'promo_code' => $promoCode,
                        'promo_discount' => $promoDiscount,
                    ]);

                    $promoCodeModel = PromoCode::where('code', $promoCode)->first();
                    if ($promoCodeModel) {

                        $userCode = UserPromoCode::with('promoCode')
                            ->where('user_id', $order->user_id)
                            ->where('code_id', $promoCodeModel->id)
                            ->first();

                        if ($userCode && $promoCodeModel->expiry === 'COUNT' && $promoCodeModel->count !== null && $userCode->count !== null) {
                            if ((int)$promoCodeModel->count <= (int)$userCode->count) {
                                return [
                                    "status" => false,
                                    "message" => "Promo Code is already used",
                                    "data" => [],
                                ];
                            }
                        }

                        $userPromoCode = UserPromoCode::firstOrCreate([
                            'user_id' => $order->user_id,
                            'code_id' => $promoCodeModel->id
                        ]);

                        $userPromoCode->update([
                            'is_used' => true,
                            'count' => ($userPromoCode->count ?? 0) + 1
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid promo code.',
                    ], 422);
                }
            }
            $order->updateQuietly([
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
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
            ], 500);
        }
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

    public function applyPromoCode($code, $order_amount)
    {
        $promoCode = PromoCode::where('code', $code)->first();

        if (!$promoCode) {
            return null;
        }
        // Tax rate from environment
        $taxRate = (float)env('TAX_RATE', 0.15);

        // The $order_amount passed is the taxable base (before VAT)
        // This should be: sub_total + delivery_fee
        $taxableBase = (float)$order_amount;

        // Max order cap for discountable amount
        $discountableAmount = $taxableBase;
        if ($promoCode->max_order != null) {
            $discountableAmount = min($taxableBase, (float)$promoCode->max_order);
        }

        // Calculate discount on the taxable base (before VAT)
        $discount = 0.0;
        if ($promoCode->promo_type == 'Percentage') {
            $percent = (float)($promoCode->discount_percentage ?? 0);
            $discount = round($discountableAmount * ($percent / 100), 2);
        } else {
            // Fixed amount
            $fixed = (float)($promoCode->discounted_amount ?? 0);
            $discount = min($fixed, $discountableAmount);
        }

        // Safety: discount cannot exceed discountable amount
        $discount = min($discount, $discountableAmount);

        $promoCode->discounted_amount = $discount;

        return $promoCode->discounted_amount;
    }
}
