<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\DriverRequestService;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Models\Referral;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use App\Models\Driver;
use App\Models\Vendor;
use App\Services\StatusSmsWhatsappService;
use App\Services\WhatsappBotWebhookService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappBotController extends Controller
{

    /**
     * Create a new user from whatsapp bot
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function createUserFromBot(Request $request)
    // {
    //     $request->validate([
    //         'first_name' => ['required', 'string', 'max:255'],
    //         'phone'      => ['required', 'regex:/^966\d{7,10}$/'],
    //     ]);

    //     $user = User::firstOrCreate(
    //         ['phone' => $request->phone],
    //         [
    //             'first_name' => $request->first_name,
    //             'status'     => 1,
    //             'app_lang'   => 'ar',
    //         ]
    //     );

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'User created or already exists',
    //         'data'    => $user,
    //     ]);
    // }

    public function createUserFromBot(Request $request)
    {
        // 1) طبع الرقم وتوحيده قبل التحقق
        $normalizedPhone = $this->normalizeSaudiPhone((string)$request->phone);

        // دمج الرقم المطبع داخل الريكوست
        $request->merge(['phone' => $normalizedPhone]);

        // 2) التحقق بعد التطبيع
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            // تنسيق نهائي يجب أن يكون 966 ثم أرقام (7-10) — عدّل المدى لو تحتاج رقم أدق
            'phone'      => ['required', 'regex:/^966\d{7,10}$/'],
        ]);

        // 3) الإنشاء أو الجلب
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'first_name' => $request->first_name,
                'status'     => 1,
                'app_lang'   => 'ar',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'User created or already exists',
            'data'    => $user,
        ]);
    }

    /**
     * تطبيع أرقام السعودية حسب القواعد المطلوبة:
     * - إزالة الفراغات والعلامات غير الرقمية
     * - إزالة '+' لو موجود
     * - 05xxxxxxx -> 9665xxxxxxx
     * - لو لا يبدأ بـ 0 أو 966 -> نضيف 966
     */
    private function normalizeSaudiPhone(string $input): string
    {
        // إزالة الفراغات والأحرف غير الرقمية مع إبقاء '+' مؤقتًا
        $input = trim($input);

        // لو يبدأ بـ + نشيلها
        if (Str::startsWith($input, '+')) {
            $input = ltrim($input, '+');
        }

        // الآن نخلي فقط الأرقام
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        // 05xxxxxxx -> 9665xxxxxxx (نشيل الـ 0 الأول فقط)
        if (Str::startsWith($digits, '05')) {
            return '966' . substr($digits, 1);
        }

        // لو لا يبدأ بـ 0 ولا بـ 966 -> نضيف 966
        if (!Str::startsWith($digits, '966') && !Str::startsWith($digits, '0')) {
            return '966' . $digits;
        }

        // لو يبدأ بـ 966 فهو جاهز
        if (Str::startsWith($digits, '966')) {
            return $digits;
        }

        // لو يبدأ بـ 0 (وما كانت 05) ما طلبت تغييره؛ نُعيده كما هو
        // (تقدر تغيّر المنطق هنا لو تبغى تعامله مثل 05)
        return $digits;
    }
    /**
     * Save user location from whatsapp bot
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function saveUserLocationFromBot(Request $request)
    {
        // نضيف التحقق من address_type
        $validated = $request->validate([
            "phone"         => ['required', 'regex:/^966\d{7,10}$/'],
            "lat"           => "required|numeric",
            "lng"           => "required|numeric",
            "address_type"  => "required|in:House,Apartment",
        ]);

        $user = User::where('phone', $validated['phone'])->firstOrFail();

        // حدّثنا موقع المستخدم في جدول users
        $user->update([
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
        ]);

        // البحث عن vendor و driver
        $vendor = $this->getAreaVendor($validated['lat'], $validated['lng']);
        if (! $vendor) {
            return response()->json([
                "status"  => false,
                "message" => "لا يوجد مندوب يغطي هذا الموقع حالياً.",
            ], 422);
        }
//        $driver = $this->getNearbyDriver($vendor->id, $validated['lat'], $validated['lng']);

        // توليد basket_no بناءً على النوع
        if ($validated['address_type'] === "House") {
            $last = UserAddress::where('address_type', 'House')->latest()->first();
            $basket_no = 'H' . ((int) str_replace('H', '', $last->basket_no ?? 'H100') + 1);
        } else {
            $last = UserAddress::where('address_type', 'Apartment')->latest()->first();
            $basket_no = 'A' . ((int) str_replace('A', '', $last->basket_no ?? 'A100') + 1);
        }

        // إنشاء أو تحديث العنوان مع vendor_id و driver_id
        $address = UserAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'lat'           => $validated['lat'],
                'lng'           => $validated['lng'],
                'address_type'  => $validated['address_type'],
                'basket_no'     => $basket_no,
                'vendor_id'     => $vendor->id,
//                'driver_id'     => $driver->id ?? null,
            ]
        );

        return response()->json([
            "status"  => true,
            "message" => "User location saved successfully",
            "data"    => $address,
        ]);
    }


    /**
     * Create a new order from whatsapp bot
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrderFromBot(Request $request)
    {
        $request->validate([
            "phone"        => ['required', 'regex:/^966\d{7,10}$/'],
            "pickup_time"  => "required",
            "instructions" => "nullable|string",
        ]);

        // get user from phone number
        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return [
                "status"  => false,
                "message" => "User not found",
            ];
        }

        $userId = $user->id;

        $isDuplicate = $this->checkDuplicateOrder($userId);
        if ($isDuplicate > 0) {
            return [
                "status"  => false,
                "message" => "Order already placed, wait for a moment for next order",
            ];
        }

        logger('Order Place API Called (BOT)');

        $address = UserAddress::where('user_id', $userId)->first();
        if (! $address || !$address->lat || !$address->lng) {
            return [
                "status" => false,
                "message" => "User location not found. Please share location first.",
            ];
        }

        $order_code = $this->getUniqueCode();

        $vendor = $this->getAreaVendor($address->lat, $address->lng);

        if ($vendor == null) {
            return [
                "status" => false,
                "message" => __('api')['order_vendor'],
                "data" => [],
            ];
        }

        $vendorId = $vendor->id ?? 0;
//        $driver   = $this->getNearbyDriver($vendorId, $address->lat, $address->lng);

        $currentDate = date('Y-m-d');

        $order = Order::create([
            "order_code"   => $order_code,
            "user_id"      => $userId,
            "address_id"   => $address->id ?? 0,
            "pickup_date"  => $currentDate,
            "pickup_time"  => $request->pickup_time,
            "dropoff_date" => date('Y-m-d', strtotime($currentDate . ' +1 day')),
            "dropoff_time" => $request->pickup_time,
            "instructions" => $request->instructions,
            "vendor_id"    => $vendorId,
//            "driver_id"    => $driver->id ?? 0,
            // "source"       => 'whatsapp',
        ]);

        OrderTracking::firstOrCreate(
            ['order_id' => $order->id, 'status' => "PLACED"],
            ['order_id' => $order->id, 'status' => "PLACED"]
        );

        //─── باقة المستخدم ───
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        if ($activePackage && $activePackage->package->hold_amount > 0) {
            $transaction = WalletTransaction::create([
                'user_id'     => $userId,
                'type'        => 'hold',
                'amount'      => $activePackage->package->hold_amount,
                'status'      => 'on_hold',
                'order_id'    => $order->id,
                'description' => 'Hold amount for order #' . $order->order_code,
            ]);

            $order->hold_transaction_id = $transaction->id;
            $order->save();
        }

        $queueName = ($activePackage && $activePackage->package->has_priority)
            ? 'high'
            : 'default';

        ProcessOrderJob::dispatch($order)->onQueue($queueName);

        $driverRequestService = new DriverRequestService();
        $pickupResult = $driverRequestService->sendPickupRequestToDrivers(
            $order,
            $vendorId,
            $address->lat,
            $address->lng
        );

        //───  Referral bonus logic ───
        // التحقق من أن هذا أول طلب للمستخدم
        $isFirstOrder = Order::where('user_id', $userId)
            ->where('status', '!=', 'DRAFT')
            ->where('status', '!=', 'CANCELLED')
            ->count() == 0;

        if ($isFirstOrder) {
            $referral = Referral::where('referred_id', $userId)->first();

            if (!$referral && $user->referral_used) {
                $referrer = User::where('referral_code', $user->referral_used)->first();

                if ($referrer) {
                    $referral = Referral::create([
                        'referrer_id' => $referrer->id,
                        'referred_id' => $userId,
                        'referral_code' => $user->referral_used,
                    ]);
                }
            }

            if ($referral && !$referral->rewarded) {
            $referrer = User::find($referral->referrer_id);
            $referred = User::find($referral->referred_id);

            if ($referrer && $referred) {
                //add 30 SAR to referrer
                $referrer->wallet()->firstOrCreate([])->transactions()->create([
                    'type' => 'credit',
                    'amount' => 30,
                    'source' => 'referral_reward',
                    'description' => 'Referral reward for inviting a user',
                ]);
                $referrer->wallet->increment('balance', 30);

                //add 30 SAR to referred
                $referred->wallet()->firstOrCreate([])->transactions()->create([
                    'type' => 'credit',
                    'amount' => 30,
                    'source' => 'referral_reward',
                    'description' => 'Referral reward for being referred',
                ]);
                $referred->wallet->increment('balance', 30);

                //rewarded
                $referral->update(['rewarded' => true]);

                //send notification to referred
                $this->sendNotifications([
                    'title' => 'You have been referred to ' . $referrer->name,
                    'message' => 'You have been rewarded with 30 SAR for being referred',
                    'user' => $referred,
                ], 'user');

                //send notification to referrer
                $this->sendNotifications([
                    'title' => 'Referral reward earned!',
                    'message' => 'You have been rewarded with 30 SAR for referring ' . $referred->name,
                    'user' => $referrer,
                ], 'user');
            }
        }
        }

        //─── Notifications ───
        $webhookService = new WhatsappBotWebhookService();
        $webhookService->sendOrderConfirmed($user->phone, $order_code);
        Log::info('Order Confirmed Webhook Sent' . $user->phone);

        try {
            $data = [
                "title"      => "Hi, " . $user->first_name . " You have placed a new order",
                "title_ar"   => "مرحبًا، " . $user->first_name . " لقد قمت بطلب جديد",
                "message"    => "Thanks for placing a new order #$order_code.",
                "message_ar" => "شكرًا لتقديم طلب جديد #$order_code.",
                "user"       => $user,
                "order"      => $order,
            ];
            $this->sendNotifications($data, 'user');

            $data = [
                "title" => "You have a new order for pickup",
                "title_ar" => "لديك طلب جديد للاستلام",
                "message" => "You have a new order for pickup",
                "message_ar" => "لديك طلب جديد للاستلام",
                "order" => $order,
                "user"  => Driver::find($order->driver_id),
            ];
            $this->sendNotifications($data, 'driver');

            $data = [
                "title"      => "There is a new order #$order->order_code.",
                "title_ar"   => "هناك طلب جديد #$order->order_code.",
                "message"    => "A new order has been placed.",
                "message_ar" => "تم استلام طلب جديد.",
                "order"      => $order,
                "user"       => Vendor::find($vendorId),
            ];
            $this->sendNotifications($data, 'vendor');

            $data = [
                "title"   => "New Order from WhatsApp",
                "message" => "Order #$order->order_code placed.",
                "link"    => "admin/order-details/" . $order->id,
            ];
            $this->sendNotifications($data, 'admin');
        } catch (\Exception $ex) {
            logger()->error("Notification Error: {$ex->getMessage()}");
        }

        return [
            "status" => true,
            "message" => "Order placed successfully!",
            "data" => [
                "order" => $order,
            ],
        ];
    }

    /**
     * Get order details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails(Request $request)
    {

        Log::info('getOrderDetails.start');
        $user = User::where('phone', $request->phone)->first();

        // 1️⃣ حساب رسوم التوصيل بناءً على الباقة النشطة
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        $deliveryFee = $activePackage
            ? $activePackage->package->delivery_fee
            : 0;

        Log::info('deliveryFee', ['deliveryFee' => $deliveryFee]);
        $order = Order::with(['orderItems.item', 'orderItems.addOns', 'deliveryAddress'])->where('id', $request->order_id)->first();
        Log::info('order', ['order' => $order]);
        $order_items = [];
        $add_ons = [];
        //    dd($order->orderItems);
        foreach ($order->orderItems as $key => $orderItem) {
            $itemData = [];
            if ($orderItem->item != null) {
                $itemData = [
                    "id" => $orderItem->item->id,
                    "name" => (isset($request->language) && $request->language == "ar") ? $orderItem->item->name_ar : $orderItem->item->name,
                    "description" => (isset($request->language) && $request->language == "ar") ? $orderItem->item->description_ar : $orderItem->item->description,
                    "image" => $orderItem->item->image,
                    "quantity" => $orderItem->quantity,
                    "price" => $orderItem->price,
                    "service_id" => $orderItem->service_id,
                ];
            }

            // إضافة الإضافات لكل عنصر
            if ($orderItem->addOns && count($orderItem->addOns) > 0) {
                $itemData['add_ons'] = [];
                foreach ($orderItem->addOns as $addOn) {
                    $itemData['add_ons'][] = [
                        "id" => $addOn->id,
                        "name" => (isset($request->language) && $request->language == "ar") ? $addOn->name_ar : $addOn->name,
                        "price" => $addOn->pivot->price ?? $addOn->price,
                    ];
                }
            }

            $order_items[] = $itemData;
            $add_ons = $orderItem->addOns;
        }
        unset($order->orderItems);
        $order->order_items = $order_items;
        $order->delivery_fee = (float) $deliveryFee;

        Log::info('order', ['order' => $order]);

        return [
            "status" => true,
            "message" => "Here is order details",
            "data" => $order,
        ];
    }

    /**
     * Get order status
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusOrder(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return [
                "status" => false,
                "message" => "User not found",
            ];
        }
        $order = Order::where('id', $request->order_id)->where('user_id', $user->id)->first();
        if (! $order) {
            return [
                "status" => false,
                "message" => "Order not found",
            ];
        }
        $status = $order->status;
        $orderCode = $order->order_code ?? ('CB' . $order->id);
        $lang = $request->header('lang') ?? $request->header('accept-language') ?? 'ar';
        $lang = strtolower(substr($lang, 0, 2));
        $dropoffDate = $order->dropoff_date ?? '';
        $dropoffTime = $order->dropoff_time ?? '';
        $deliveryTextAr = ($dropoffDate && $dropoffTime) ? "اليوم المتوقع للتسليم: {$dropoffDate} بين {$dropoffTime}." : "اليوم بين 6-10 مساء.";
        $deliveryTextEn = ($dropoffDate && $dropoffTime) ? "Expected delivery: {$dropoffDate} between {$dropoffTime}." : "Today between 6-10 PM.";
        $statusAr = config('order_status_ar')[$status] ?? $status;
        $statusEn = $status;

        if ($lang === 'ar') {
            if (in_array($status, ['PLACED', 'PROCESSING', 'DRAFT'])) {
                $message = "- قيد المعالجة: طلبك [{$orderCode}] قيد المعالجة، وسيكون جاهزاً للتسليم خلال 24 ساعة.";
            } elseif (in_array($status, ['PICKED_UP', 'ARRIVED'])) {
                $message = "- في الطريق: طلبك [{$orderCode}] في الطريق إلى المغسلة.";
            } elseif (in_array($status, ['READY_TO_DELIVER', 'PICKED_FOR_DELIVER', 'CONFIRMED_PAID'])) {
                $message = "- جاهز للتسليم: طلبك [{$orderCode}] جاهز ومجدول للتسليم. {$deliveryTextAr}";
            } elseif ($status === 'DELIVERED') {
                $message = "- تم التسليم: طلبك [{$orderCode}] تم تسليمه بنجاح.";
            } elseif ($status === 'CANCELLED') {
                $message = "- تم الإلغاء: طلبك [{$orderCode}] تم إلغاؤه. لمزيد من التفاصيل يرجى التواصل مع الدعم.";
            } else {
                $message = "- حالة أخرى: طلبك [{$orderCode}] في حالة: {$statusAr}. يرجى التواصل مع الدعم لمزيد من التفاصيل.";
            }
        } else {
            if (in_array($status, ['PLACED', 'PROCESSING', 'DRAFT'])) {
                $message = "- Processing: Your order [{$orderCode}] is being processed and will be ready for delivery within 24 hours.";
            } elseif (in_array($status, ['PICKED_UP', 'ARRIVED'])) {
                $message = "- On the way: Your order [{$orderCode}] is on the way to the laundry.";
            } elseif (in_array($status, ['READY_TO_DELIVER', 'PICKED_FOR_DELIVER', 'CONFIRMED_PAID'])) {
                $message = "- Ready for delivery: Your order [{$orderCode}] is ready and scheduled for delivery. {$deliveryTextEn}";
            } elseif ($status === 'DELIVERED') {
                $message = "- Delivered: Your order [{$orderCode}] has been delivered successfully.";
            } elseif ($status === 'CANCELLED') {
                $message = "- Cancelled: Your order [{$orderCode}] has been cancelled. For more details, please contact support.";
            } else {
                $message = "- Other status: Your order [{$orderCode}] is in status: {$statusEn}. Please contact support for more details.";
            }
        }

        return [
            "status" => true,
            "message" => $message,
        ];
    }


    /**
     * Update order instructions
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderInstructions(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return [
                "status" => false,
                "message" => "User not found",
            ];
        }
        $order = Order::where('id', $request->order_id)->where('user_id', $user->id)->first();
        if (! $order) {
            return [
                "status" => false,
                "message" => "Order not found",
            ];
        }
        $order->instructions = $request->instructions;
        $order->update();
        return [
            "status" => true,
            "message" => "Order instructions updated successfully",
            "data" => $order->instructions,
        ];
    }

    /**
     * Get unique code for order
     * @return string
     */
    public function getUniqueCode()
    {
        $order = Order::latest()->first();
        $number = $order->id ?? 0;
        return 'CB' . $number + 1;
    }

    /**
     * Check if the user has a duplicate order
     * @param int $userId
     * @return int
     */
    public function checkDuplicateOrder($userId)
    {
        $currentTime = Carbon::now()->format('H:i');
        $order = Order::where(['user_id' => $userId])->whereRaw("DATE_FORMAT(created_at, '%H:%i') = ?", [$currentTime])->count();
        return $order;
    }

    /**
     * Get the vendor for the user's location
     * @param float $userLat
     * @param float $userLng
     * @return \App\Models\Vendor|null
     */
    public function getAreaVendor($userLat, $userLng)
    {
        $vendors = Vendor::where([
            'status'      => 1,
            'is_approved' => 1,
            'deleted_at'  => null
        ])->get();

        foreach ($vendors as $vendor) {
            if ($this->isPointInPolygon($vendor->areas, $userLat, $userLng)) {
                return $vendor;
            }
        }

        return null;
    }


    public function sendWhatsappMessage(Request $request, StatusSmsWhatsappService $sms)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'name' => ['required', 'string'],
        ]);

        // إن أردت: طبّق normalize هنا أو داخل السيرفس
        $resp = $sms->customerSignup($data['name'], $data['phone']);

        return response()->json($resp);
    }
}
