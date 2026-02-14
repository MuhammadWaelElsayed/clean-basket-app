<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PDFController;
use App\Services\DriverRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Vendor;
use App\Models\Company;
use App\Models\CompanyNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorNotification;
use App\Services\FCMService;
use App\Models\Notification;
use App\Models\DriverNotification;
use App\Models\User;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\OrderTracking;
use App\Models\DriverRequest;
use App\Models\Item;
use App\Services\LeajlakService;
use App\Services\StatusSmsWhatsappService;
use App\Services\WhatsappBotWebhookService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class VendorController extends Controller
{

    public function login(Request $request)
    {
        $request->validate(["phone" => 'required', 'password' => 'required']);

        $user = Vendor::where('phone', $request['phone'])
            // ->where('status', 1)->where('is_approved',1)
            ->first();
        if ($user == null) {
            return [
                'status' => false,
                'message' => __('api')['login_wrong'],
                'data' => [],
            ];
        }
        if ($user->status == 0) {
            return [
                'status' => false,
                'message' => __('api')['account_inactive'],
                'data' => [],
            ];
        }
        if ($user->is_approved == 0) {
            return [
                'status' => false,
                'message' => __('api')['account_approved'],
                'data' => [],
            ];
        }
        $validCredentials = Hash::check($request['password'], $user->password);
        // dd($user->password);
        if ($validCredentials) {
            $user->tokens()->delete();
            $token = $user->createToken('vendors-token')->plainTextToken;

            if (isset($request->deviceToken) && $request->deviceToken !== null) {
                Vendor::find($user->id)->update(['deviceToken' => $request->deviceToken, 'api_token' => $token]);
            }
            return [
                'status' => true,
                'message' => 'Login Success!',
                'data' => [
                    "auth_token" => $token,
                    "vendor" => $user
                ],
            ];
        } else {
            return [
                'status' => false,
                'message' => __('api')['incorrect_password'],
                'data' => [],
            ];
        }
    }

    public function signout()
    {
        // dd(auth()->user());
        auth()->user()->tokens()->delete();

        Vendor::find(auth()->user()->id)->update(['deviceToken' => null, 'api_token' => null]);

        return [
            "status" => true,
            'message' => 'You Logout successfully',
            "data" => []
        ];
    }

    public function getMyOrders(Request $request)
    {
        $vendor = Vendor::whereId($request->user()->id)->first();
        $orders = Order::with(['user'])->latest()
            ->where(['vendor_id' => $vendor->id]);

        if (isset($request->status)) {
            $status = strtolower($request->status);
            if ($status === "active") {
                $orders->whereNotIn('status', ['DELIVERED', 'CANCELLED']);
            } else {
                $orders->whereIn('status', ['DELIVERED']);
            }
        }
        if (isset($request->date_from)) {
            $orders->whereDate('created_at', '>=', $request->date_from);
        }
        if (isset($request->date_to)) {
            $orders->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $orders->paginate(15);

        if ($orders) {
            return [
                'status' => true,
                'message' => "Data get successfully!",
                'data' => [
                    "orders" => $orders->items(),
                    "pagination" => [
                        'current_page' => $orders->currentPage(),
                        'per_page' => $orders->perPage(),
                        'total_records' => $orders->total(),
                        'last_page' => $orders->lastPage(),
                    ]
                ],
            ];
        }
    }

    public function getOrderDetails(Request $request)
    {
        $request->validate([
            "order_id" => "required",
        ]);

        $order = Order::with(['user', 'deliveryAddress', 'orderItems.item', 'orderItems.addOns' , 'orderItems.serviceType'])
            ->where([
                'vendor_id' => auth()->user()->id,
                'id' => $request->order_id
            ])->first();

        if (! $order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order not found!',
            ], 404);
        }

        $order_items = [];

        foreach ($order->orderItems as $item) {
            if ($item->item != null) {
                $itemArray = [
                    "id"          => $item->item->id,
                    "name"        => (isset($request->language) && $request->language == "ar") ? $item->item->name_ar : $item->item->name,
                    "description" => (isset($request->language) && $request->language == "ar") ? $item->item->description_ar : $item->item->description,
                    "image"       => $item->item->image,
                    "quantity"    => $item->quantity,
                    "service_id"  => $item->service_id,
                    "service_type" => $item->serviceType->name,
                ];

                if ($item->addOns && count($item->addOns) > 0) {
                    $itemArray['add_ons'] = [];
                    foreach ($item->addOns as $addOn) {
                        $itemArray['add_ons'][] = [
                            "id"    => $addOn->id,
                            "name"  => (isset($request->language) && $request->language == "ar") ? $addOn->name_ar : $addOn->name,
                            "price" => $addOn->pivot->price ?? $addOn->price,
                        ];
                    }
                }

                $order_items[] = $itemArray;
            }
        }

        unset($order->orderItems);
        $order->order_items = count($order_items) > 0 ? $order_items : null;

        return response()->json([
            'status'  => true,
            'message' => "Data retrieved successfully!",
            'data'    => [
                "order" => $order,
            ],
        ]);
    }


    // public function updateOrderStatus(Request $request)
    // {
    //     $request->validate([
    //         "order_id" => "required",
    //         "status" => "required|in:PROCESSING,READY_TO_DELIVER",
    //     ]);

    //     $order = Order::with(['user'])->findOrFail($request->order_id);
    //     //Put Conditions on Order status
    //     if ($request->status == "PROCESSING" && $order->status != 'ARRIVED') {
    //         return [
    //             "status" => false,
    //             "message" => __('api')['processing_error'],
    //         ];
    //     }

    //     if ($request->status == "PROCESSING") {
    //         $request->validate([
    //             "order_items" => "required|array",
    //         ]);
    //         $sub_total = 0;
    //         foreach ($request->order_items as $item) {
    //             $item_obj = Item::find($item['item_id']);
    //             $price = $item_obj->price;
    //             $lineTotal = $price * $item['quantity'];
    //             Log::info("Order {$order->id} lineTotal: {$lineTotal}");
    //             OrderItem::create([
    //                 "item_id" => $item['item_id'],
    //                 "quantity" => $item['quantity'],
    //                 "service_type" => $item['type'] ?? '',
    //                 "price" => $price,
    //                 "order_id" => $order->id,
    //                 "total_price" => $lineTotal,
    //             ]);
    //             $sub_total += $price * $item['quantity'];
    //         }
    //         //Calculate Fees deliver, vat etc
    //         $delivery_fee = Setting::where('key', 'delivery_charges')->pluck('value')->first();
    //         $vat = Setting::where('key', 'vat')->pluck('value')->first();
    //         $vat_amount = ($sub_total / 100) * $vat;
    //         $grand_total = floatval($sub_total) + floatval($delivery_fee ?? 0) + floatval($vat_amount);
    //         //Calculate Vendor Commission
    //         $commission = auth()->user()->commission;
    //         $commission_amount = ($sub_total / 100) * $commission;

    //         $order->update([
    //             'sub_total' => $sub_total,
    //             'delivery_fee' => $delivery_fee ?? 0,
    //             'vat' => $vat_amount,
    //             'grand_total' => $grand_total,
    //             'commission_amount' => $commission_amount,
    //         ]);

    //         $webhookService = new WhatsappBotWebhookService();
    //         $webhookService->sendOrderStarted($order->user->phone);
    //         Log::info('Order Started Webhook Sent' . $order->user->phone);

    //         $data = [
    //             "title" => "Your order #$order->order_code status is updated and waiting for your action.",
    //             "title_ar" => "تم تحديث حالة طلبك #$order->order_code وهو في انتظار إجراءك.",
    //             "message" => "Your order #$order->order_code status is updated and waiting for your action.",
    //             "message_ar" => "تم تحديث حالة طلبك #$order->order_code وهو في انتظار إجراءك.",
    //             "user" => $order->user,
    //             "order" => $order,
    //         ];
    //         $this->sendNotifications($data, 'user');
    //         PDFController::createPDF($order);
    //     }

    //     // if($request->status=="READY_TO_DELIVER"){
    //     //     $rules=[
    //     //         'order_image' => 'required|mimes:png,jpg,jpeg,gif'
    //     //     ];
    //     //     if($request->language=="ar"){
    //     //         $customMessages = [
    //     //             'required' => 'حقل :attribute مطلوب.',
    //     //             'mimes' => 'يجب أن يكون :attribute من نوع: :values.',
    //     //         ];
    //     //         $customAttributes = [
    //     //             'order_image' => 'صورة الطلب',
    //     //         ];
    //     //         $request->validate($rules, $customMessages, $customAttributes);
    //     //     }else{
    //     //         $request->validate($rules);
    //     //     }



    //     //     $data=[
    //     //         "title" => "Order #$order->order_code is ready to deliver.",
    //     //         "title_ar" => "الطلب #$order->order_code جاهز للتسليم.",
    //     //         "message" => "Order #$order->order_code is ready to deliver.",
    //     //         "message_ar" => "الطلب #$order->order_code جاهز للتسليم.",
    //     //         "order" => $order,
    //     //     ];
    //     //     $data['user']=Driver::find($order->driver_id);
    //     //     $this->sendNotifications($data,'driver');
    //     //     $data=[
    //     //         "title" => "Your order #$order->order_code is ready to deliver.",
    //     //         "title_ar" => "طلبك #$order->order_code جاهز للتسليم.",
    //     //         "message" => "Your order #$order->order_code is ready to deliver.",
    //     //         "message_ar" => "طلبك #$order->order_code جاهز للتسليم.",
    //     //         "order" => $order,
    //     //         "user"=> $order->user
    //     //     ];
    //     //     $this->sendNotifications($data,'user');
    //     //     $image = $request->file('order_image');
    //     //     $imageName='';
    //     //     if($image){
    //     //         $imageName = $this->optimizeImage($image);
    //     //     }
    //     //     $order->update(['order_image'=>$imageName]);

    //     // }
    //     if ($request->status == "READY_TO_DELIVER") {
    //         $rules = [
    //             'order_image' => 'required|mimes:png,jpg,jpeg,gif'
    //         ];

    //         if ($request->language == "ar") {
    //             $customMessages = [
    //                 'required' => 'حقل :attribute مطلوب.',
    //                 'mimes' => 'يجب أن يكون :attribute من نوع: :values.',
    //             ];
    //             $customAttributes = [
    //                 'order_image' => 'صورة الطلب',
    //             ];
    //             $request->validate($rules, $customMessages, $customAttributes);
    //         } else {
    //             $request->validate($rules);
    //         }

    //         // تحميل الطلب مع العلاقات المطلوبة
    //         $order = Order::with(['user', 'vendor', 'deliveryAddress'])->findOrFail($request->order_id);

    //         // ✅ إذا كانت المغسلة ضمن القائمة المسموح لها باستخدام Leajlak
    //         $allowedVendorIds = [70, 3, 5]; // ضع هنا IDs المغاسل التي تريد إرسال الطلب منها إلى Leajlak
    //         if (in_array($order->vendor_id, $allowedVendorIds)) {

    //             // إرسال الطلب إلى Leajlak
    //             LeajlakService::sendOrderToLeajlak($order);

    //             // إشعار المستخدم فقط
    //             if ($order->user) {
    //                 $data = [
    //                     "title" => "Your order #$order->order_code is ready to deliver.",
    //                     "title_ar" => "طلبك #$order->order_code جاهز للتسليم.",
    //                     "message" => "Your order #$order->order_code is ready to deliver.",
    //                     "message_ar" => "طلبك #$order->order_code جاهز للتسليم.",
    //                     "order" => $order,
    //                     "user" => $order->user
    //                 ];
    //                 Log::info('Send Notification Data (Leajlak):', $data);
    //                 $this->sendNotifications($data, 'user');
    //             }
    //         } else {
    //             // 👉 إرسال الطلب إلى السائق
    //             $data = [
    //                 "title" => "Order #$order->order_code is ready to deliver.",
    //                 "title_ar" => "الطلب #$order->order_code جاهز للتسليم.",
    //                 "message" => "Order #$order->order_code is ready to deliver.",
    //                 "message_ar" => "الطلب #$order->order_code جاهز للتسليم.",
    //                 "order" => $order,
    //                 "user" => Driver::find($order->driver_id)
    //             ];
    //             $this->sendNotifications($data, 'driver');

    //             // إشعار المستخدم
    //             $data['user'] = $order->user;
    //             $this->sendNotifications($data, 'user');
    //         }

    //         // رفع صورة الطلب
    //         $image = $request->file('order_image');
    //         $imageName = '';
    //         if ($image) {
    //             $imageName = $this->optimizeImage($image);
    //         }
    //         $order->update(['order_image' => $imageName]);
    //     }


    //     $order->update(['status' => $request->status]);

    //     OrderTracking::firstOrCreate(['order_id' => $request->order_id, 'status' => $request->status], [
    //         'order_id' => $request->order_id,
    //         'status' => $request->status,
    //     ]);
    //     return [
    //         'status' => true,
    //         'message' => "Order Status updated successfully!",
    //         "data" => [
    //             "order" => $order,
    //         ]
    //     ];
    // }

    public function updateOrderStatus(Request $request)
    {
        // 1. التحقق من المدخلات الأساسية
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'status'   => 'required|in:PROCESSING,READY_TO_DELIVER,ARRIVED',
        ]);

        // 2. جلب الطلب والعلاقات الضرورية
        $order = Order::with(['user', 'items', 'deliveryAddress'])->findOrFail($request->order_id);

        // 3. شروط تغيير الحالة
        if ($request->status === 'PROCESSING' && $order->status !== 'ARRIVED') {
            return response()->json([
                'status'  => false,
                'message' => __('api.processing_error'),
            ], 422);
        }

        if($request->status == 'READY_TO_DELIVER') {
            $result = (new DriverRequestService())->sendDeliveryRequestToDrivers(
                $order,
                $order->vendor_id,
                $order->deliveryAddress->lat,
                $order->deliveryAddress->lng
            );
        }

        if ($request->status == 'ARRIVED') {
            $data = [
                "title" => "You order #$order->order_code has been ARRIVED",
                "title_ar" => "You order #$order->order_code has been ARRIVED",
                "message" => "You order #$order->order_code has been ARRIVED",
                "message_ar" => "You order #$order->order_code has been ARRIVED",
                "order" => $order,
                "user" => $order->vendor,
            ];
            $this->sendNotifications($data, 'vendor');
        }
        // 4. حالة PROCESSING: إضافة/تحديث العناصر وحساب الأسعار
        if ($request->status === 'PROCESSING') {
            $request->validate([
                'order_items' => 'required|array',
                'order_items.*.item_id'   => 'required|integer|exists:items,id',
                'order_items.*.quantity'  => 'required|integer|min:1',
                'order_items.*.type'      => 'nullable|string',
            ]);

            DB::transaction(function () use ($request, $order) {
                // حذف العناصر القديمة
                OrderItem::where('order_id', $order->id)->delete();

                $subTotal = 0;

                // إضافة العناصر الجديدة مع حساب total_price لكل سطر
                foreach ($request->order_items as $it) {
                    $item    = Item::findOrFail($it['item_id']);
                    $price   = $item->price;
                    $qty     = $it['quantity'];
                    $lineTotal = $price * $qty;

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'item_id'      => $it['item_id'],
                        'service_type' => $it['type'] ?? '',
                        'price'        => $price,
                        'quantity'     => $qty,
                        'total_price'  => $lineTotal,
                    ]);

                    $subTotal += $lineTotal;
                    Log::info("Order {$order->id} - Line total for item {$it['item_id']}: {$lineTotal}");
                }

                // جلب الإعدادات وحساب الضرائب والرسوم
                $deliveryFee     = Setting::where('key', 'delivery_charges')->value('value') ?? 0;
                $vatPercent      = Setting::where('key', 'vat')->value('value') ?? 0;
                $vatAmount       = ($subTotal * $vatPercent) / 100;
                $grandTotal      = $subTotal + $deliveryFee + $vatAmount;
                $commissionPercent = auth()->user()->commission;
                $commissionAmount  = ($subTotal * $commissionPercent) / 100;

                // تحديث حقول الطلب
                $order->update([
                    'sub_total'         => $subTotal,
                    'delivery_fee'      => $deliveryFee,
                    'vat'               => $vatAmount,
                    'grand_total'       => $grandTotal,
                    'commission_amount' => $commissionAmount,
                ]);
            });

            // إعادة تحميل العلاقة بعد التحديث
            $order->load('items');

            // إرسال إشعارات وWebhooks وPDF
            (new WhatsappBotWebhookService())->sendOrderStarted($order->user->phone);
            Log::info("Order Started Webhook sent to {$order->user->phone}");

            $notificationData = [
                'title'     => "Your order #{$order->order_code} is now PROCESSING.",
                'title_ar'  => "تم بدء معالجة طلبك #{$order->order_code}.",
                'message'   => "Your order #{$order->order_code} is now PROCESSING.",
                'message_ar' => "تم بدء معالجة طلبك #{$order->order_code}.",
                'user'      => $order->user,
                'order'     => $order,
            ];
            $this->sendNotifications($notificationData, 'user');
            PDFController::createPDF($order);
        }

        // 5. حالة READY_TO_DELIVER: رفع صورة وإرسال الطلب للسائق أو لمغسلة Leajlak
        if ($request->status === 'READY_TO_DELIVER') {
            // التحقق من رفع الصورة
            $rules = ['order_image' => 'required|mimes:png,jpg,jpeg,gif'];
            if ($request->language === 'ar') {
                $customMessages = [
                    'required' => 'حقل :attribute مطلوب.',
                    'mimes'    => 'يجب أن يكون :attribute من نوع: :values.',
                ];
                $customAttributes = ['order_image' => 'صورة الطلب'];
                $request->validate($rules, $customMessages, $customAttributes);
            } else {
                $request->validate($rules);
            }

            // إعادة تحميل الطلب مع العلاقات
            $order->load(['user', 'vendor', 'deliveryAddress']);

            // قائمة المغاسل المسموح لها بـ Leajlak
            $allowedVendors = [];
            if (in_array($order->vendor_id, $allowedVendors, true)) {
                LeajlakService::sendOrderToLeajlak($order);

                $notify = [
                    'title'     => "Your order #{$order->order_code} is ready to deliver.",
                    'title_ar'  => "طلبك #{$order->order_code} جاهز للتسليم.",
                    'message'   => "Your order #{$order->order_code} is ready to deliver.",
                    'message_ar' => "طلبك #{$order->order_code} جاهز للتسليم.",
                    'user'      => $order->user,
                    'order'     => $order,
                ];
                Log::info('Leajlak notification data:', $notify);
                $this->sendNotifications($notify, 'user');
            } else {
                // إرسال للسائق أولاً
                $driver = Driver::find($order->driver_id);
                $notify = [
                    'title'     => "Order #{$order->order_code} is ready to deliver.",
                    'title_ar'  => "الطلب #{$order->order_code} جاهز للتسليم.",
                    'message'   => "Order #{$order->order_code} is ready to deliver.",
                    'message_ar' => "الطلب #{$order->order_code} جاهز للتسليم.",
                    'user'      => $driver,
                    'order'     => $order,
                ];
                $this->sendNotifications($notify, 'driver');
                // ثم إشعار المستخدم
                $notify['user'] = $order->user;
                $this->sendNotifications($notify, 'user');
            }

            // رفع ومعالجة صورة الطلب
            if ($image = $request->file('order_image')) {
                $imageName = $this->optimizeImage($image);
                $order->update(['order_image' => $imageName]);
            }
        }

        // 6. أخيراً: تحديث حالة الطلب وتتبع الحالة
        $order->update(['status' => $request->status]);
        OrderTracking::firstOrCreate(
            ['order_id' => $order->id, 'status' => $request->status],
            ['order_id' => $order->id, 'status' => $request->status]
        );

        return response()->json([
            'status'  => true,
            'message' => __('api.order_status_updated'),
            'data'    => ['order' => $order],
        ]);
    }

    public function updateOrderStatusIfSortingByClient(Request $request)
    {
        // 1. التحقق من المدخلات الأساسية
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'status'   => 'required|in:PROCESSING,READY_TO_DELIVER',
        ]);

        // 2. جلب الطلب مع العلاقات
        $order = Order::with(['user', 'items'])->findOrFail($request->order_id);

        // 3. التحقق من الحالة السابقة إذا أراد التحويل إلى PROCESSING
        if ($request->status === 'PROCESSING' && $order->status !== 'ARRIVED') {
            return response()->json([
                'status'  => false,
                'message' => __('api.processing_error'),
            ], 422);
        }

        // 4. حالة PROCESSING: فقط إشعارات و PDF بدون أي تعديل مالي أو عناصر
        if ($request->status === 'PROCESSING') {
            $order->load('items');

            (new WhatsappBotWebhookService())->sendOrderStarted($order->user->phone);
            Log::info("Order Started Webhook sent to {$order->user->phone}");

            $notificationData = [
                'title'      => "Your order #{$order->order_code} is now PROCESSING.",
                'title_ar'   => "تم بدء معالجة طلبك #{$order->order_code}.",
                'message'    => "Your order #{$order->order_code} is now PROCESSING.",
                'message_ar' => "تم بدء معالجة طلبك #{$order->order_code}.",
                'user'       => $order->user,
                'order'      => $order,
            ];
            $this->sendNotifications($notificationData, 'user');
            PDFController::createPDF($order);
        }

        // 5. حالة READY_TO_DELIVER: رفع صورة وإرسال الإشعارات فقط
        if ($request->status === 'READY_TO_DELIVER') {
            $rules = ['order_image' => 'required|mimes:png,jpg,jpeg,gif'];
            if ($request->language === 'ar') {
                $customMessages = [
                    'required' => 'حقل :attribute مطلوب.',
                    'mimes'    => 'يجب أن يكون :attribute من نوع: :values.',
                ];
                $customAttributes = ['order_image' => 'صورة الطلب'];
                $request->validate($rules, $customMessages, $customAttributes);
            } else {
                $request->validate($rules);
            }

            $order->load(['user', 'vendor', 'deliveryAddress']);

            $allowedVendors = [];
            if (in_array($order->vendor_id, $allowedVendors, true)) {
                LeajlakService::sendOrderToLeajlak($order);

                $notify = [
                    'title'      => "Your order #{$order->order_code} is ready to deliver.",
                    'title_ar'   => "طلبك #{$order->order_code} جاهز للتسليم.",
                    'message'    => "Your order #{$order->order_code} is ready to deliver.",
                    'message_ar' => "طلبك #{$order->order_code} جاهز للتسليم.",
                    'user'       => $order->user,
                    'order'      => $order,
                ];
                Log::info('Leajlak notification data:', $notify);
                $this->sendNotifications($notify, 'user');
            } else {
                $driver = Driver::find($order->driver_id);
                $notify = [
                    'title'      => "Order #{$order->order_code} is ready to deliver.",
                    'title_ar'   => "الطلب #{$order->order_code} جاهز للتسليم.",
                    'message'    => "Order #{$order->order_code} is ready to deliver.",
                    'message_ar' => "الطلب #{$order->order_code} جاهز للتسليم.",
                    'user'       => $driver,
                    'order'      => $order,
                ];
                $this->sendNotifications($notify, 'driver');

                // إشعار المستخدم بعد إشعار السائق
                $notify['user'] = $order->user;
                $this->sendNotifications($notify, 'user');
            }

            // حفظ صورة الطلب
            if ($image = $request->file('order_image')) {
                $imageName = $this->optimizeImage($image);
                $order->update(['order_image' => $imageName]);
            }
        }

        // 6. تحديث حالة الطلب وتتبعها
        $order->update(['status' => $request->status]);
        OrderTracking::firstOrCreate(
            ['order_id' => $order->id, 'status' => $request->status],
            ['order_id' => $order->id, 'status' => $request->status]
        );

        return response()->json([
            'status'  => true,
            'message' => __('api.order_status_updated'),
            'data'    => ['order' => $order],
        ]);
    }


    public function sendCompanyNotification($order, $status)
    {
        CompanyNotification::create([
            "company_id" => $order->company_id,
            "title" => "Order with ID #" . $order->order_code . " is " . $status . " by your driver",
            "link" => url('company/order-details/' . $order->id),
        ]);
    }

    public function sendNotification($title, $body, $data, $user)
    {
        $response = FCMService::sendWithClick(
            $user->deviceToken,
            [
                'title' => $title,
                'body' => $body

            ],
            $data
        );
    }

    public function getProfile(Request $request)
    {
        //    dd('here');
        $user = Vendor::findOrFail(auth()->user()->id)->first();

        if ($user) {
            return [
                'status' => true,
                'message' => "Profile get successfully!",
                'data' => $user,
            ];
        }
    }

    public function updateItemStatus(Request $request)
    {
        $request->validate([
            "order_id" => "required",
            "item_status" => "required|in:Wash,Press",
        ]);

        $order = Order::findOrFail($request->order_id);
        if ($order->status != 'CONFIRMED_PAID') {
            return [
                'status' => false,
                'message' => "Sorry, Order is not confirmed from customer",
                "data" => []
            ];
        }
        $order->update(['item_status' => $request->item_tatus]);

        return [
            'status' => true,
            'message' => "Order Items status updated successfully!",
            "data" => $order
        ];
    }

    public function updateOrderItems(Request $request)
    {
        // 1. التحقق الأساسي
        $request->validate([
            'order_id'     => 'required|integer|exists:orders,id',
            'order_items'  => 'required|array|min:1',
            'order_items.*.item_id'  => 'required|integer|exists:items,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.type'     => 'nullable|string',
        ]);

        // 2. جلب الطلب
        $order = Order::findOrFail($request->order_id);

        // 3. منع التعديل بعد التأكيد والدفع
        if ($order->status === 'CONFIRMED_PAID') {
            return response()->json([
                'status'  => false,
                'message' => 'Sorry, order is already confirmed and paid; items cannot be updated.',
            ], 422);
        }

        // 4. بدء المعاملة
        DB::transaction(function () use ($order, $request) {
            // حذف العناصر القديمة
            OrderItem::where('order_id', $order->id)->delete();

            $subTotal = 0;

            // إضافة العناصر الجديدة وحساب total_price لكل سطر
            foreach ($request->order_items as $it) {
                $item      = Item::findOrFail($it['item_id']);
                $price     = $item->price;
                $qty       = $it['quantity'];
                $lineTotal = $price * $qty;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'item_id'      => $it['item_id'],
                    'service_type' => $it['type'] ?? '',
                    'price'        => $price,
                    'quantity'     => $qty,
                    'total_price'  => $lineTotal,
                ]);

                $subTotal += $lineTotal;
            }

            // جلب الإعدادات وحساب الضريبة والرسوم
            $deliveryFee      = Setting::where('key', 'delivery_charges')->value('value') ?? 0;
            $vatPercent       = Setting::where('key', 'vat')->value('value') ?? 0;
            $vatAmount        = ($subTotal * $vatPercent) / 100;
            $grandTotal       = $subTotal + $deliveryFee + $vatAmount;
            $commissionPct    = auth()->user()->commission;
            $commissionAmount = ($subTotal * $commissionPct) / 100;

            // تحديث حقول الطلب
            $order->update([
                'sub_total'         => $subTotal,
                'delivery_fee'      => $deliveryFee,
                'vat'               => $vatAmount,
                'grand_total'       => $grandTotal,
                'commission_amount' => $commissionAmount,
            ]);
        });

        // 5. إعادة تحميل العناصر المرفقة
        $order->load('items');

        // 6. إرجاع النتيجة
        return response()->json([
            'status'  => true,
            'message' => 'Order items updated successfully!',
            'data'    => ['order' => $order],
        ]);
    }

    // public function updateOrderItems(Request $request)
    // {
    //     $request->validate([
    //         "order_id" => "required",
    //         "order_items" => "required|array",
    //     ]);
    //     $order = Order::findOrFail($request->order_id);
    //     if ($order->status == 'CONFIRMED_PAID') {
    //         return [
    //             'status' => false,
    //             'message' => "Sorry, Order is proceed, now you can't update items",
    //         ];
    //     }

    //     $sub_total = 0;
    //     OrderItem::where(['order_id' => $order->id])->delete();
    //     foreach ($request->order_items as $item) {
    //         $item_obj = Item::find($item['item_id']);
    //         $price = $item_obj->price;
    //         $lineTotal = $price * $item['quantity'];
    //         OrderItem::create([
    //             "item_id" => $item['item_id'],
    //             "quantity" => $item['quantity'],
    //             "service_type" => $item['type'] ?? '',
    //             "price" => $price,
    //             "order_id" => $order->id,
    //             "total_price" => $lineTotal
    //         ]);
    //         $sub_total += $price * $item['quantity'];
    //     }
    //     //Calculate Fees deliver, vat etc
    //     $delivery_fee = Setting::where('key', 'delivery_charges')->pluck('value')->first();
    //     $vat = Setting::where('key', 'vat')->pluck('value')->first();
    //     $vat_amount = ($sub_total / 100) * $vat;
    //     $grand_total = $sub_total + $delivery_fee + $vat_amount;
    //     //Calculate Vendor Commission
    //     $commission = auth()->user()->commission;
    //     $commission_amount = ($sub_total / 100) * $commission;

    //     $order->update([
    //         'sub_total' => $sub_total,
    //         'delivery_fee' => $delivery_fee,
    //         'vat' => $vat_amount,
    //         'grand_total' => $grand_total,
    //         'commission_amount' => $commission_amount,
    //     ]);

    //     return [
    //         'status' => true,
    //         'message' => "Order Items updated successfully!",
    //         "data" => $order
    //     ];
    // }

    public function getNotifcations(Request $request)
    {
        if (isset($request->language) && $request->language == "ar") {
            $title = "title_ar as title";
            $message = "message_ar as message";
        } else {
            $title = "title";
            $message = "message";
        }
        $notification = VendorNotification::select('id', $title, $message, 'created_at')->where('vendor_id', $request->user()->id);

        if (isset($request->unread) &&  $request->unread == 1) {
            $notification->where('is_read', 0);
        }

        $notification = $notification->latest('id')->get();
        if ($notification) {
            return [
                'status' => true,
                'message' => "Notifications get successfully",
                'data' => $notification,
            ];
        }
    }

    public function markasReadNotifcation(Request $request)
    {
        $request->validate([
            "notification_id" => "required",
        ]);
        $notification = VendorNotification::findOrFail($request->notification_id)->update([
            "is_read" => 1
        ]);
        if ($notification) {
            return [
                'status' => true,
                'message' => "Success! Notification is removed",
                'data' => [],
            ];
        }
    }
    public function clearAllNotifications(Request $request)
    {

        $notification = VendorNotification::where(['vendor_id' => auth()->user()->id])->delete();

        return [
            'status' => true,
            'message' => "All notifications is cleared",
            'data' => [],
        ];
    }

    public function optimizeImage($upImage)
    {
        $maxWidth = 1000;
        $maxHeight = 1000;

        // Get image dimensions
        list($width, $height) = getimagesize($upImage);

        // Calculate the new dimensions while maintaining aspect ratio
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        // Create a new image resource
        $image = imagecreatetruecolor($newWidth, $newHeight);

        // Determine the image type (JPEG, PNG, GIF)
        $imageType = exif_imagetype($upImage);

        // Load the original image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($upImage);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($upImage);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($upImage);
                break;
            default:
                dd('Unsupported image type');
        }

        // Resize and save the optimized image
        imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $imageName = uniqid() . '_order_img.jpg';
        $outputPath = public_path('uploads/' . $imageName); // You can choose a different format if needed
        imagejpeg($image, $outputPath, 80); // Adjust the quality (0-100) as needed

        // Clean up resources
        imagedestroy($image);
        imagedestroy($source);

        return $imageName;
    }
}
