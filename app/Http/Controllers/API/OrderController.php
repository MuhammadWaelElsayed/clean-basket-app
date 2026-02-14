<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Jobs\ProcessOrderJob;
use App\Models\AddOn;
use App\Services\DriverRequestService;
use App\Services\OsrmService;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\Driver;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\OrderPriority;
use App\Models\OrderTracking;
use App\Models\PaymentLog;
use App\Models\Referral;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\FCMService;
use App\Services\VoucherService;
use App\Http\Controllers\API\ServiceFeeSettingsController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Calculate service fee for an order
     *
     * @param float $subTotal
     * @param float $taxAmount
     * @return array
     */
    private function calculateServiceFee(float $subTotal, float $taxAmount = 0): array
    {
        $serviceFeeController = new ServiceFeeSettingsController();
        return $serviceFeeController->calculateServiceFee($subTotal, $taxAmount);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            "pickup_date" => "required",
            "pickup_time" => "required",
            "dropoff_date" => "required",
            "dropoff_time" => "required",
            'is_carpet' => 'nullable',
        ]);

        $is_carpet = $request->input('is_carpet', false);

        $user = auth()->user();

        if (blank($user->phone)) {
            return response()->json(['status' => false, 'message' => "No phone found, please contact support", 'data' => []], 400);
        }

        $userId = auth()->user()->id;
        $isDuplicate = $this->checkDuplicateOrder($userId);
        if ($isDuplicate > 0) {
            return [
                "status" => false,
                "message" => "Order already placed, wait for a moment for next order",
            ];
        }
        logger('Order Place API Called');

        $address = UserAddress::where('user_id', $userId)->first();

        $vendor = $this->getAreaVendor($address->lat, $address->lng);

        if ($vendor == null) {
            return [
                "status" => false,
                "message" => __('api')['order_vendor'],
                "data" => [],
            ];
        }
        $vendorId = $vendor->id;

        $order = Order::create([
            "order_code" => 'CB' . ((Order::latest()->first()->id ?? 0) + 1),
            "user_id" => $userId,
            "address_id" => $address->id ?? 0,
            "pickup_date" => $request->pickup_date,
            "pickup_time" => $request->pickup_time,
            "dropoff_date" => $request->dropoff_date,
            "dropoff_time" => $request->dropoff_time,
            "instructions" => $request->instructions,
            "vendor_id" => $vendorId,
//            "driver_id" => $driver->id ?? 0,
            'sorting' => 'vendor',
            'is_carpet' => $is_carpet,
        ]);
        OrderTracking::firstOrCreate(['order_id' => $order->id, 'status' => "PLACED"], [
            'order_id' => $order->id,
            'status' => "PLACED",
        ]);

        //─── hold amount from package if exists ───
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        if ($activePackage && $activePackage->package->hold_amount > 0) {
            $transaction = WalletTransaction::create([
                'user_id' => $userId,
                'type' => 'hold',
                'amount' => $activePackage->package->hold_amount,
                'status' => 'on_hold',
                'order_id' => $order->id,
                'description' => 'Hold amount for order #' . $order->order_code,
            ]);

            $order->hold_transaction_id = $transaction->id;
            $order->save();
        }

        //─── Dispatch processing job with priority queue ───
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

        //─── Notifications ─────────────────────────────────
        try {
            $data = [
                "title" => "Hi, " . auth()->user()->name . " You have placed a new order",
                "title_ar" => "مرحبًا، " . auth()->user()->name . " لقد قمت بطلب جديد",
                "message" => "Thanks for placing a new order #{$order->order_code}. We will update you soon on it.",
                "message_ar" => "شكرًا لتقديم طلب جديد #{$order->order_code}. سنقوم بتحديثك قريبًا بشأنه.",

                "user" => auth()->user(),
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
            $vendor = Vendor::find($vendorId);
            $data['user'] = $vendor;
            $this->sendNotifications($data, 'vendor');

            $data = [
                "title" => "New Order is recieved from " . auth()->user()->name,
                "message" => "A new order #$order->order_code. check its more details.",
                "link" => "admin/order-details/" . $order->id,
            ];
            $this->sendNotifications($data, 'admin');
        } catch (\Exception $ex) {
            // dd($ex->getMessage());
        }
        return [
            "status" => true,
            "message" => "Order placed successfully! ",
            "data" => [
                "order" => $order,
                'vendor' => $vendor,
                'pickup_result' => 'Find Drivers Job dispatched',
            ],
        ];
    }

    public function confirmOrder(Request $request)
    {
        Log::info('confirmOrder request: ' . json_encode($request->all()));
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_response' => 'required|array',
            'promo_code' => 'sometimes|string',
        ]);

        $user = auth()->user();
        // نحمّل علاقة عنوان التوصيل الصحيحة بدل العلاقة غير الموجودة "address"
        $order = Order::with(['deliveryAddress', 'vendor', 'driver'])->findOrFail($request->order_id);

        // 1️⃣ منع التكرار إذا كانت الدفعة قد تمت مسبقًا
        if ($order->pay_status === 'Paid') {
            return response()->json([
                'status' => false,
                'message' => 'This order has already been paid.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            // 2️⃣ سجل ردّ بوابة الدفع داخل المعاملة
            PaymentLog::create([
                'order_id' => $order->id,
                'response' => json_encode($request->payment_response),
            ]);

            // 3️⃣ التحقق من نجاح الدفع
            $invoiceStatus = $request->payment_response['InvoiceStatus'] ?? null;
            if ($invoiceStatus !== 'Paid') {
                throw new \Exception('Payment unsuccessful.', 422);
            }

            // 3️⃣1️⃣ حساب الإجمالي الكامل أولاً (بدون خصم)
            $taxRate = env('TAX_RATE', 0);
            $serviceFee = (float)($order->service_fee ?? 0);
            // حساب الضريبة على المبلغ بدون service_fee
            $baseAmountBeforeServiceFee = $order->sub_total + $order->delivery_fee;
            $taxAmountBeforeDiscount = round($baseAmountBeforeServiceFee * $taxRate, 2);
            $baseAmountBeforeDiscount = $baseAmountBeforeServiceFee + $serviceFee;
            $grandTotalBeforeDiscount = round($baseAmountBeforeDiscount + $taxAmountBeforeDiscount, 2);
            Log::info('grandTotalBeforeDiscount', ['value' => $grandTotalBeforeDiscount, 'service_fee' => $serviceFee]);

            // 4️⃣ تطبيق كوبون الخصم على الإجمالي الكامل
            $promoCode = $request->promo_code ?? null;
            $promoDiscount = 0;
            if ($promoCode) {
                $promoDiscount = $this->applyPromoCode($promoCode, $baseAmountBeforeServiceFee);
                if ($promoDiscount <= 0) {
                    throw new \Exception('Invalid promo code.', 422);
                }
            }

            // 5️⃣ حساب المبلغ النهائي بعد الخصم
            // الخصم يطبق فقط على baseAmountBeforeServiceFee (sub_total + delivery_fee)
            $baseAmount = max(0, $baseAmountBeforeServiceFee - $promoDiscount);
            $taxAmount = round($baseAmount * $taxRate, 2);
            // service_fee لا يخضع للخصم، يُضاف بعد الخصم
            $baseAmountWithServiceFee = $baseAmount + $serviceFee;
            $grandTotal = round($baseAmountWithServiceFee + $taxAmount, 2);

            // 6️⃣ التحقق من المبلغ المرسل في payment_response
            $paymentAmount = $request->payment_response['PaidAmount'] ?? $request->payment_response['Amount'] ?? 0;
            $paymentAmount = (float)$paymentAmount;

            // تحويل من هللة إلى ريال (PaidAmount يأتي بالهللة)
            if ($paymentAmount > 100) {
                $paymentAmount = $paymentAmount / 100;
            }

            Log::info('Payment verification', [
                'payment_amount' => $paymentAmount,
                'grand_total' => $grandTotal,
                'difference' => $grandTotal - $paymentAmount
            ]);

            // 7️⃣ التعامل مع الدفع الجزئي من المحفظة والباقة
            $partialPaymentFromWallet = false;
            $partialPaymentFromPackage = false;
            $fromPackage = 0;
            $fromWallet = 0;

            // إذا كان المبلغ المرسل أقل من الإجمالي، نحتاج لدفع جزئي
            if ($paymentAmount < $grandTotal) {
                $remainingAmount = $grandTotal - $paymentAmount;

                $activePackage = $user->userPackages()
                    ->where('is_active', true)
                    ->latest('start_date')
                    ->with('package')
                    ->first();

                $packageCredit = $activePackage->remaining_credit ?? 0;
                $walletBalance = $user->wallet->balance ?? 0;

                // حساب المبالغ المطلوب خصمها من المحفظة والباقة
                $fromPackage = $request->payment_response['from_package'] ?? 0;
                $fromWallet = $request->payment_response['from_wallet'] ?? 0;

                // إذا لم يتم تحديد المبالغ، احسب تلقائياً من المحفظة
                if ($fromPackage == 0 && $fromWallet == 0) {
                    $fromWallet = $remainingAmount; // خصم كامل المبلغ المتبقي من المحفظة
                }

                // التحقق من أن مجموع المبالغ المطلوب خصمها يساوي المبلغ المتبقي
                $totalPartialPayment = $fromPackage + $fromWallet;
                if (abs($totalPartialPayment - $remainingAmount) > 0.01) {
                    throw new \Exception("Partial payment amounts don't match remaining amount. Required: {$remainingAmount}, Provided: {$totalPartialPayment}", 422);
                }

                // تنفيذ الخصم من الباقة
                if ($fromPackage > 0 && $activePackage && $packageCredit >= $fromPackage) {
                    $vatForPackage = 0;
                    $netPackage = $fromPackage - $vatForPackage;
                    $txnIdPkg = 'PAK-' . now()->format('YmdHis') . '-' . Str::random(4);

                    $activePackage->decrement('remaining_credit', $fromPackage);
                    if ($activePackage->remaining_credit == 0) {
                        $activePackage->update(['is_active' => false]);
                    }

                    $activePackage->transactions()->create([
                        'type' => 'debit',
                        'amount' => $netPackage,
                        'vat_amount' => $vatForPackage,
                        'description' => 'Partial order payment from package',
                        'related_order_id' => $order->id,
                        'transaction_id' => $txnIdPkg,
                    ]);

                    // سجل في معاملات المحفظة كمصدر package للتتبع
                    $user->wallet->transactions()->create([
                        'type' => 'debit',
                        'amount' => $netPackage,
                        'vat_amount' => $vatForPackage,
                        'source' => 'package',
                        'description' => 'Partial payment from package',
                        'related_order_id' => $order->id,
                        'transaction_id' => $txnIdPkg,
                    ]);

                    $partialPaymentFromPackage = true;

                    // إشعار العميل عند الخصم من الباقة
                    $this->sendNotifications([
                        'title' => 'Amount deducted from your package',
                        'title_ar' => 'تم خصم مبلغ من باقاتك',
                        'message' => "Amount {$fromPackage} SAR has been deducted from your package for order #{$order->order_code}.",
                        'message_ar' => "تم خصم مبلغ {$fromPackage} ريال من باقاتك مقابل طلب رقم {$order->order_code}.",
                        'user' => $user,
                        'order' => $order,
                    ], 'user');
                }

                // تنفيذ الخصم من المحفظة
                if ($fromWallet > 0 && $walletBalance >= $fromWallet) {
                    $vatForWallet = 0;
                    $netWallet = $fromWallet - $vatForWallet;
                    $txnIdWal = 'WAL-' . now()->format('YmdHis') . '-' . Str::random(4);

                    $user->wallet->decrement('balance', $fromWallet);

                    $user->wallet->transactions()->create([
                        'type' => 'debit',
                        'amount' => $netWallet,
                        'vat_amount' => $vatForWallet,
                        'source' => 'wallet',
                        'description' => 'Partial payment from wallet',
                        'related_order_id' => $order->id,
                        'transaction_id' => $txnIdWal,
                    ]);

                    $partialPaymentFromWallet = true;

                    // إشعار العميل عند الخصم من المحفظة
                    $this->sendNotifications([
                        'title' => 'Amount deducted from your wallet',
                        'title_ar' => 'تم خصم مبلغ من محفظتك',
                        'message' => "Amount {$fromWallet} SAR has been deducted from your wallet for order #{$order->order_code}.",
                        'message_ar' => "تم خصم مبلغ {$fromWallet} ريال من محفظتك مقابل طلب رقم {$order->order_code}.",
                        'user' => $user,
                        'order' => $order,
                    ], 'user');
                }
            } elseif ($paymentAmount > $grandTotal) {
                // إذا كان المبلغ المرسل أكبر من الإجمالي
                throw new \Exception("Payment amount ({$paymentAmount}) exceeds order total ({$grandTotal})", 422);
            }

            $amount = $grandTotal;

            // Log::info('confirmOrder.taxCalculation', compact('baseAmount', 'taxRate', 'taxAmount', 'amount'));

            // 8️⃣ تأكد من وجود عنوان التوصيل
            $address = $order->deliveryAddress;
            if (!$address) {
                throw new \Exception('Delivery address not found.', 500);
            }

            // 9️⃣ تعيين البائع والسائق إذا لم يكونا معيَّنين سابقًا
            if (!$order->vendor_id || !$order->driver_id) {
                $lat = $address->lat;
                $lng = $address->lng;
                $vendor = $this->getAreaVendor($lat, $lng);
                if (!$vendor) {
                    throw new \Exception(__('api.order_vendor'), 422);
                }
//                $driver = $this->getNearbyDriver($vendor->id, $lat, $lng);
            } else {
                // إذا كانا معيَّنين مسبقًا
                $vendor = $order->vendor;
//                $driver = $order->driver;
            }

            // 🔟 تحديث الطلب دفعة واحدة مع الحالة الجديدة
            $order->update([
                'vendor_id' => $vendor->id,
//                'driver_id' => $driver->id ?? null,
                'promo_code' => $promoCode,
                'promo_discount' => $promoDiscount,
                'pay_status' => 'Paid',
                'vat' => $taxAmount,
                'grand_total' => $amount,
                // 'status'           => 'PLACED',
                'payment_response' => json_encode([
                    'InvoiceStatus' => 'Paid',
                    'promo_discount' => $promoDiscount,
                    'amount_paid' => $amount,
                    'partial_payment' => ($partialPaymentFromWallet || $partialPaymentFromPackage),
                    'from_package' => $fromPackage,
                    'from_wallet' => $fromWallet,
                ]),
            ]);

            // تحديث payment_logs لحفظ معاملات ميسر
            $paymentLog = PaymentLog::where('order_id', $order->id)->first();
            if ($paymentLog) {
                if (!$partialPaymentFromWallet && !$partialPaymentFromPackage) {
                    // الدفع كاملاً من ميسر
                    $paymentLog->update([
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'vat_amount' => $taxAmount,
                        'payment_method' => 'maysar',
                        'transaction_id' => $request->payment_response['InvoiceId'] ?? 'MAY-' . now()->format('YmdHis'),
                        'payment_reference' => $request->payment_response['InvoiceTransactions'][0]['TransactionId'] ?? null,
                        'status' => 'paid'
                    ]);
                } else {
                    // الدفع الجزئي - تحديث payment_logs بالمبلغ المدفوع من ميسر
                    $paymentLog->update([
                        'user_id' => $user->id,
                        'amount' => $paymentAmount,
                        'vat_amount' => round($paymentAmount * $taxRate / (1 + $taxRate), 2), // حساب الضريبة على المبلغ المدفوع
                        'payment_method' => 'maysar_partial',
                        'transaction_id' => $request->payment_response['InvoiceId'] ?? 'MAY-' . now()->format('YmdHis'),
                        'payment_reference' => $request->payment_response['InvoiceTransactions'][0]['TransactionId'] ?? null,
                        'status' => 'paid',
                        'partial_payment' => true,
                        'from_package' => $fromPackage,
                        'from_wallet' => $fromWallet
                    ]);
                }
            }

            if ($order->status == 'DRAFT') {
                $order->update(['status' => 'PLACED']);
                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'PLACED',
                ]);
            }


            // 🔟 إشعارات العميل
            $this->sendNotifications([
                'title' => 'Your order has been paid',
                'title_ar' => 'تم دفع طلبك',
                'message' => "You have paid order #{$order->order_code}.",
                'message_ar' => "لقد دفعت طلبك رقم {$order->order_code}.",
                'user' => $user,
                'order' => $order,
                'mail' => ['template' => 'confirm_order'],
            ], 'user');

            // 1️⃣1️⃣ إشعار الإدارة
            $this->sendNotifications([
                'title' => "Order #{$order->order_code} has been paid",
                'message' => "Order #{$order->order_code} paid by {$user->name}.",
                'link' => "admin/order-details/{$order->id}",
            ], 'admin');

            $result = (new DriverRequestService())->sendPickupRequestToDrivers(
                $order,
                $order->vendor_id,
                $order->deliveryAddress->lat,
                $order->deliveryAddress->lng
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order confirmed successfully!',
                'data' => ['order' => $order],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("confirmOrder failed: {$e->getMessage()}");

            $status = $e->getCode() === 422 ? 422 : 500;
            $message = $e->getCode() === 422
                ? $e->getMessage()
                : 'Payment processing error. Please try again.';

            return response()->json([
                'status' => false,
                'message' => $message,
            ], $status);
        }
    }

    public function payUsingPackageAndWallet(Request $request)
    {
        // Log::info('payUsingPackageAndWallet.start', ['request' => $request->all()]);

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'promo_code' => 'sometimes|string',
        ]);

        $user = auth()->user();

        // نجلب الباقة النشطة مبكرًا (لا مانع بدون قفل)
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        // نبدأ معاملة مبكرًا كي نتمكن من قفل الطلب أثناء المعالجة
        DB::beginTransaction();

        try {
            // نجلب الطلب مع قفل لتفادي السباقات عند التحديث
            $order = Order::with('deliveryAddress')
                ->where('id', $request->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->pay_status === 'Paid') {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'This order has already been paid.',
                ], 409);
            }

            // 1️⃣ حساب رسوم التوصيل والضرائب والخصم
            // إذا كان الطلب Partial، نستخدم القيم الحالية كما هي (لا نعيد الحساب)
            if ($order->pay_status === 'Partial' && $order->due_amount > 0) {
                $deliveryFee = $order->delivery_fee;
                $serviceFee = (float)($order->service_fee ?? 0);
                $taxRate = env('TAX_RATE', 0);
                $taxAmount = $order->vat;
                $grandTotal = $order->grand_total;
                $promoCode = $order->promo_code;
                $promoDiscount = $order->promo_discount;

                $baseAmount = $order->sub_total + $deliveryFee + $serviceFee - $promoDiscount;

                // قيم للعرض فقط - حساب الضريبة بدون service_fee
                $baseAmountBeforeServiceFee = $order->sub_total + $deliveryFee;
                $taxAmountBeforeDiscount = round($baseAmountBeforeServiceFee * $taxRate, 2);
                $baseAmountBeforeDiscount = $baseAmountBeforeServiceFee + $serviceFee;
                $grandTotalBeforeDiscount = round($baseAmountBeforeDiscount + $taxAmountBeforeDiscount, 2);
            } else {
                // طلب جديد أو DRAFT: نحسب من الصفر
                $deliveryFee = $activePackage
                    ? $activePackage->package->delivery_fee
                    : 0;
                $serviceFee = (float)($order->service_fee ?? 0);
                Log::info('deliveryFee', ['value' => $deliveryFee, 'service_fee' => $serviceFee]);

                $taxRate = env('TAX_RATE', 0);
                // حساب الضريبة بدون service_fee
                $baseAmountBeforeServiceFee = $order->sub_total + $deliveryFee;
                $taxAmountBeforeDiscount = round($baseAmountBeforeServiceFee * $taxRate, 2);
                $baseAmountBeforeDiscount = $baseAmountBeforeServiceFee + $serviceFee;
                $grandTotalBeforeDiscount = round($baseAmountBeforeDiscount + $taxAmountBeforeDiscount, 2);

                // تطبيق الكوبون إن وُجد
                $promoCode = $request->promo_code ?? null;
                $promoDiscount = 0;
                if ($promoCode) {
                    $promoDiscount = $this->applyPromoCode($promoCode, $baseAmountBeforeServiceFee);
                    if ($promoDiscount <= 0) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Invalid promo code.',
                        ], 422);
                    }
                }

                // حساب المبلغ بعد الخصم - الضريبة تُحسب بدون service_fee
                // الخصم يطبق فقط على baseAmountBeforeServiceFee (sub_total + delivery_fee)
                $baseAmount = max(0, $baseAmountBeforeServiceFee - $promoDiscount);
                $taxAmount = round($baseAmount * $taxRate, 2);
                // service_fee لا يخضع للخصم، يُضاف بعد الخصم
                $baseAmountWithServiceFee = $baseAmount + $serviceFee;
                $grandTotal = round($baseAmountWithServiceFee + $taxAmount, 2);
            }

            // 2️⃣ الأرصدة المتاحة
            $packageCredit = $activePackage->remaining_credit ?? 0;
            $walletBalance = $user->wallet->balance ?? 0;

            // 3️⃣ توزيع الدفع المتوقع
            $fromPackage = 0;
            $fromWallet = 0;
            $dueAmount = 0; // سيتم ضبطه لاحقًا
            $payStatus = '';

            if ($order->pay_status === 'Partial' && $order->due_amount > 0) {
                // في حالة Partial: نعتمد على المبلغ المتبقي حاليًا
                $remainingAmount = round($order->due_amount, 2);

                Log::info('Partial Order Debug', [
                    'order_id' => $order->id,
                    'remainingAmount' => $remainingAmount,
                    'packageCredit' => $packageCredit,
                    'walletBalance' => $walletBalance,
                    'condition1' => $packageCredit >= $remainingAmount,
                    'condition2' => $walletBalance >= $remainingAmount
                ]);

                if ($packageCredit >= $remainingAmount) {
                    $fromPackage = $remainingAmount;
                    $fromWallet = 0;
                    $dueAmount = 0;
                    $payStatus = 'Paid';
                } elseif ($walletBalance >= $remainingAmount) {
                    $fromPackage = 0;
                    $fromWallet = $remainingAmount;
                    $dueAmount = 0;
                    $payStatus = 'Paid';
                } else {
                    $fromPackage = $packageCredit;
                    $fromWallet = $walletBalance;
                    $dueAmount = max(0, round($remainingAmount - $packageCredit - $walletBalance, 2));
                    $payStatus = $dueAmount > 0 ? 'Partial' : 'Paid';
                }
            } elseif ($packageCredit >= $grandTotal) {
                $fromPackage = $grandTotal;
                $fromWallet = 0;
                $dueAmount = 0;
                $payStatus = 'Paid';
            } elseif ($walletBalance >= $grandTotal) {
                $fromPackage = 0;
                $fromWallet = $grandTotal;
                $dueAmount = 0;
                $payStatus = 'Paid';
            } else {
                $fromPackage = $packageCredit;
                $fromWallet = $walletBalance;
                $dueAmount = max(0, round($grandTotal - $packageCredit - $walletBalance, 2));
                $payStatus = $dueAmount > 0 ? 'Partial' : 'Paid';
            }

            // 4️⃣ الضرائب المفصولة لكل مصدر (مطفأة حاليًا)
            $vatForPackage = 0;
            $vatForWallet = 0;

            // 5️⃣ مبالغ صافية/مجملة لما سندفعه الآن
            $netPackage = $fromPackage - $vatForPackage;
            $netWallet = $fromWallet - $vatForWallet;
            $paidTotal = $netPackage + $netWallet;
            $paidGross = round($fromPackage + $fromWallet, 2);

            // 6️⃣ في حالة الدفع الجزئي المتوقع، نُرجع الحساب فقط دون خصم
            if ($payStatus === 'Partial') {
                // لا ننفّذ خصومات فعلية هنا — فقط إرجاع الحساب
                DB::commit(); // لا تغييرات دائمة تمت، لكن لا ضرر من الإنهاء النظيفة
                return response()->json([
                    'status' => true,
                    'message' => 'Partial payment calculation completed. Use confirmOrder to complete payment.',
                    'data' => [
                        'order_details' => [
                            'order_id' => $order->id,
                            'sub_total' => $order->sub_total,
                            'delivery_fee' => $deliveryFee,
                            'service_fee' => $serviceFee,
                            'service_fee_applied' => (bool)($order->service_fee_applied ?? false),
                            'base_amount_before_discount' => $baseAmountBeforeDiscount,
                            'tax_rate' => $taxRate,
                            'tax_amount_before_discount' => $taxAmountBeforeDiscount,
                            'grand_total_before_discount' => $grandTotalBeforeDiscount,
                        ],
                        'promo_details' => [
                            'promo_code' => $promoCode,
                            'promo_discount' => $promoDiscount,
                        ],
                        'final_calculation' => [
                            'base_amount' => $baseAmount,
                            'tax_amount' => $taxAmount,
                            'grand_total' => $grandTotal,
                        ],
                        'available_balances' => [
                            'package_credit' => $packageCredit,
                            'wallet_balance' => $walletBalance,
                        ],
                        'payment_distribution' => [
                            'from_package_gross' => $fromPackage,
                            'from_wallet_gross' => $fromWallet,
                            'vat_for_package' => $vatForPackage,
                            'vat_for_wallet' => $vatForWallet,
                            'paid_gross' => $paidGross,
                            'paid_net' => $paidTotal,
                            'due_amount' => $dueAmount,
                            'pay_status' => $payStatus,
                        ],
                        'package_info' => $activePackage ? [
                            'package_id' => $activePackage->package->id,
                            'package_name' => $activePackage->package->name,
                            'remaining_credit' => $packageCredit,
                            'delivery_fee' => $deliveryFee,
                        ] : null,
                    ],
                ], 200);
            }

            // 7️⃣ تنفيذ الخصومات فعليًا (وصلنا هنا يعني الدفع كامل الآن)
            // خصم من الباقة
            if ($fromPackage > 0 && $activePackage) {
                $netPackage = $fromPackage - $vatForPackage;
                $txnIdPkg = 'PAK-' . now()->format('YmdHis') . '-' . Str::random(4);

                $activePackage->decrement('remaining_credit', $fromPackage);
                if ($activePackage->remaining_credit == 0) {
                    $activePackage->update(['is_active' => false]);
                }

                $activePackage->transactions()->create([
                    'type' => 'debit',
                    'amount' => $netPackage,
                    'vat_amount' => $vatForPackage,
                    'description' => 'Order payment from package',
                    'related_order_id' => $order->id,
                    'transaction_id' => $txnIdPkg,
                ]);

                // سجل مرجعي داخل معاملات المحفظة
                $user->wallet->transactions()->create([
                    'type' => 'debit',
                    'amount' => $netPackage,
                    'vat_amount' => $vatForPackage,
                    'source' => 'package',
                    'description' => 'Paid from package',
                    'related_order_id' => $order->id,
                    'transaction_id' => $txnIdPkg,
                ]);

                // إشعار
                $this->sendNotifications([
                    'title' => 'Amount deducted from your package',
                    'title_ar' => 'تم خصم مبلغ من باقاتك',
                    'message' => "Amount {$fromPackage} SAR has been deducted from your package for order #{$order->order_code}.",
                    'message_ar' => "تم خصم مبلغ {$fromPackage} ريال من باقاتك مقابل طلب رقم {$order->order_code}.",
                    'user' => $user,
                    'order' => $order,
                ], 'user');
            }

            // خصم من المحفظة
            if ($fromWallet > 0) {
                $netWallet = $fromWallet - $vatForWallet;
                $txnIdWal = 'WAL-' . now()->format('YmdHis') . '-' . Str::random(4);

                $user->wallet->decrement('balance', $fromWallet);

                $user->wallet->transactions()->create([
                    'type' => 'debit',
                    'amount' => $netWallet,
                    'vat_amount' => $vatForWallet,
                    'source' => 'wallet',
                    'description' => 'Paid from wallet',
                    'related_order_id' => $order->id,
                    'transaction_id' => $txnIdWal,
                ]);

                // إشعار
                $this->sendNotifications([
                    'title' => 'Amount deducted from your wallet',
                    'title_ar' => 'تم خصم مبلغ من محفظتك',
                    'message' => "Amount {$fromWallet} SAR has been deducted from your wallet for order #{$order->order_code}.",
                    'message_ar' => "تم خصم مبلغ {$fromWallet} ريال من محفظتك مقابل طلب رقم {$order->order_code}.",
                    'user' => $user,
                    'order' => $order,
                ], 'user');
            }

            // 8️⃣ إعادة حساب المتبقي بعد الخصم الفعلي (⚠️ هنا كان الخطأ)
            // لو كان الطلب Partial سابقًا، نخصم مما هو متبقي فعلاً، وليس من grand_total
            $netPackage = ($fromPackage > 0 && $activePackage) ? ($fromPackage - $vatForPackage) : 0;
            $netWallet = ($fromWallet > 0) ? ($fromWallet - $vatForWallet) : 0;
            $paidTotal = $netPackage + $netWallet;
            $paidGross = round($fromPackage + $fromWallet, 2);

            if ($order->pay_status === 'Partial') {
                // كان متبقيًا سابقًا:
                $dueAmount = max(0, round(($order->due_amount ?? 0) - $paidGross, 2));
            } else {
                // طلب جديد: نطرح من الإجمالي الجديد المحتسب
                $dueAmount = max(0, round($grandTotal - $paidGross, 2));
            }
            $payStatus = $dueAmount > 0 ? 'Partial' : 'Paid';

            // 9️⃣ إتمام أي حجز سابق للمبلغ
            if ($order->hold_transaction_id) {
                WalletTransaction::where('id', $order->hold_transaction_id)
                    ->where('status', 'on_hold')
                    ->update(['status' => 'completed']);
            }

            // 🔟 التأكد من وجود العنوان
            $address = $order->deliveryAddress;
            if (!$address) {
                throw new \Exception('Delivery address not found.', 500);
            }
            $lat = $address->lat;
            $lng = $address->lng;

            // 1️⃣1️⃣ تحديد البائع والسائق
            $vendor = $this->getAreaVendor($lat, $lng);
            if (!$vendor) {
                throw new \Exception('Vendor not found.', 422);
            }
//            $driver = $this->getNearbyDriver($vendor->id, $lat, $lng);

            // 1️⃣2️⃣ تحديث الطلب
            if ($order->pay_status === 'Partial') {
                // كان Partial سابقاً: نحدِّث فقط المتبقي وحالة الدفع والريسبونس
                Log::info('Updating Partial Order', [
                    'order_id' => $order->id,
                    'old_due_amount' => $order->due_amount,
                    'new_due_amount' => $dueAmount,
                    'old_pay_status' => $order->pay_status,
                    'new_pay_status' => $payStatus,
                    'from_wallet' => $fromWallet,
                    'from_package' => $fromPackage
                ]);

                $order->update([
                    'due_amount' => $dueAmount,
                    'pay_status' => $payStatus,
                    'payment_response' => json_encode([
                        'InvoiceStatus' => 'Paid',
                        'promo_discount' => $promoDiscount,
                        'amount_paid' => $fromPackage + $fromWallet,
                        'partial_payment' => false,
                        'from_package' => $fromPackage,
                        'from_wallet' => $fromWallet,
                    ]),
                    // لن نُعيد ضبط vendor/driver هنا كي لا نغير تعيين سابق إن وُجد
                ]);
            } else {
                // طلب جديد: نحدّث كل الحقول
                $order->update([
                    'delivery_fee' => $deliveryFee,
                    'promo_code' => $promoCode,
                    'promo_discount' => $promoDiscount,
                    'vat' => $taxAmount,
                    'grand_total' => $grandTotal,
                    'due_amount' => $dueAmount,
                    'pay_status' => $payStatus,
                    'vendor_id' => $vendor->id,
//                    'driver_id' => $driver->id ?? null,
                    'payment_response' => json_encode([
                        'InvoiceStatus' => 'Paid',
                        'promo_discount' => $promoDiscount,
                        'amount_paid' => $fromPackage + $fromWallet,
                        'partial_payment' => $payStatus === 'Partial',
                        'from_package' => $fromPackage,
                        'from_wallet' => $fromWallet,
                    ]),
                    // 'status' => 'PLACED', // ستُضبط أدناه إذا كان DRAFT
                ]);
            }

            // 1️⃣3️⃣ ترقية حالة الطلب من DRAFT إلى PLACED عند إتمام أول دفع
            if ($order->status == 'DRAFT') {
                $order->update(['status' => 'PLACED']);
                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'PLACED',
                ]);
            }

            // 1️⃣4️⃣ حفظ لوج بالدفع
            PaymentLog::create([
                'order_id' => $order->id,
                'response' => json_encode([
                    'from_package_gross' => $fromPackage,
                    'vat_for_package' => $vatForPackage,
                    'from_wallet_gross' => $fromWallet,
                    'vat_for_wallet' => $vatForWallet,
                    'paid_gross' => $paidGross,
                    'paid_net' => ($netPackage + $netWallet),
                    'due_amount' => $dueAmount,
                    'pay_status' => $payStatus,
                ]),
            ]);

            $result = (new DriverRequestService())->sendPickupRequestToDrivers(
                $order,
                $order->vendor_id,
                $order->deliveryAddress->lat,
                $order->deliveryAddress->lng
            );


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order processed successfully!',
                'data' => [
                    'order' => $order->fresh(), // تأكد من إعادة القيم المحدثة
                    'due_amount' => $dueAmount,
                    'pay_status' => $payStatus,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            $status = $e->getCode() === 422 ? 422 : 500;
            $message = $e->getCode() === 422
                ? $e->getMessage()
                : 'Payment processing error.';

            Log::error('Payment failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $message,
            ], $status);
        }
    }


    public function calculatePaymentPreview(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'promo_code' => 'sometimes|string',
        ]);

        $user = auth()->user();
        $order = Order::with('deliveryAddress')->findOrFail($request->order_id);

        if ($order->pay_status === 'Paid') {
            return response()->json([
                'status' => false,
                'message' => 'This order has already been paid.',
            ], 409);
        }

        // 1️⃣ حساب رسوم التوصيل
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        $deliveryFee = $activePackage
            ? $activePackage->package->delivery_fee
            : 0;

        // 2️⃣ حساب الإجمالي الكامل أولاً (بدون خصم)
        $taxRate = env('TAX_RATE', 0);
        $serviceFee = (float)($order->service_fee ?? 0);
        // حساب الضريبة بدون service_fee
        $baseAmountBeforeServiceFee = $order->sub_total + $deliveryFee;
        $taxAmountBeforeDiscount = round($baseAmountBeforeServiceFee * $taxRate, 2);
        $baseAmountBeforeDiscount = $baseAmountBeforeServiceFee + $serviceFee;
        $grandTotalBeforeDiscount = round($baseAmountBeforeDiscount + $taxAmountBeforeDiscount, 2);

        // 3️⃣ حساب خصم الكوبون على الإجمالي الكامل
        $promoCode = $request->promo_code ?? null;
        $promoDiscount = 0;
        if ($promoCode) {
            $promoDiscount = $this->applyPromoCode($promoCode, $baseAmountBeforeServiceFee);
            if ($promoDiscount <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid promo code.',
                ], 422);
            }
        }

        // 4️⃣ حساب المبلغ النهائي بعد الخصم - الضريبة تُحسب بدون service_fee
        // الخصم يطبق فقط على baseAmountBeforeServiceFee (sub_total + delivery_fee)
        $baseAmount = max(0, $baseAmountBeforeServiceFee - $promoDiscount);
        $taxAmount = round($baseAmount * $taxRate, 2);
        // service_fee لا يخضع للخصم، يُضاف بعد الخصم
        $baseAmountWithServiceFee = $baseAmount + $serviceFee;
        $grandTotal = round($baseAmountWithServiceFee + $taxAmount, 2);

        // 5️⃣ حساب المبالغ المتاحة
        $packageCredit = $activePackage->remaining_credit ?? 0;
        $walletBalance = $user->wallet->balance ?? 0;

        // 6️⃣ حساب التوزيع المتوقع
        $fromPackage = 0;
        $fromWallet = 0;
        $dueAmount = 0;
        $payStatus = '';

        if ($packageCredit >= $grandTotal) {
            // الباقة تكفي كامل المبلغ
            $fromPackage = $grandTotal;
            $fromWallet = 0;
            $dueAmount = 0;
            $payStatus = 'Paid';
        } elseif ($walletBalance >= $grandTotal) {
            // المحفظة تكفي كامل المبلغ
            $fromPackage = 0;
            $fromWallet = $grandTotal;
            $dueAmount = 0;
            $payStatus = 'Paid';
        } else {
            // لا تكفي أيٌّ منهما لوحدها ⇒ نسحب كل المتاح
            $fromPackage = $packageCredit;
            $fromWallet = $walletBalance;
            $dueAmount = max(0, $grandTotal - $packageCredit - $walletBalance);
            $payStatus = $dueAmount > 0 ? 'Partial' : 'Paid';
        }

        // 7️⃣ حساب الضرائب لكل جزء (معطلة حالياً)
        $vatForPackage = 0;
        $vatForWallet = 0;

        // 8️⃣ حساب المبالغ الصافية
        $netPackage = $fromPackage - $vatForPackage;
        $netWallet = $fromWallet - $vatForWallet;
        $paidTotal = $netPackage + $netWallet;
        $paidGross = round($fromPackage + $fromWallet, 2);

        return response()->json([
            'status' => true,
            'message' => 'Payment calculation completed successfully!',
            'data' => [
                'order_details' => [
                    'order_id' => $order->id,
                    'sub_total' => $order->sub_total,
                    'delivery_fee' => $deliveryFee,
                    'service_fee' => $serviceFee,
                    'service_fee_applied' => (bool)($order->service_fee_applied ?? false),
                    'base_amount_before_discount' => $baseAmountBeforeDiscount,
                    'tax_rate' => $taxRate,
                    'tax_amount_before_discount' => $taxAmountBeforeDiscount,
                    'grand_total_before_discount' => $grandTotalBeforeDiscount,
                ],
                'promo_details' => [
                    'promo_code' => $promoCode,
                    'promo_discount' => $promoDiscount,
                ],
                'final_calculation' => [
                    'base_amount' => $baseAmount,
                    'tax_amount' => $taxAmount,
                    'grand_total' => $grandTotal,
                ],
                'available_balances' => [
                    'package_credit' => $packageCredit,
                    'wallet_balance' => $walletBalance,
                ],
                'payment_distribution' => [
                    'from_package_gross' => $fromPackage,
                    'from_wallet_gross' => $fromWallet,
                    'vat_for_package' => $vatForPackage,
                    'vat_for_wallet' => $vatForWallet,
                    'paid_gross' => $paidGross,
                    'paid_net' => $paidTotal,
                    'due_amount' => $dueAmount,
                    'pay_status' => $payStatus,
                ],
                'package_info' => $activePackage ? [
                    'package_id' => $activePackage->package->id,
                    'package_name' => $activePackage->package->name,
                    'remaining_credit' => $packageCredit,
                    'delivery_fee' => $deliveryFee,
                ] : null,
            ],
        ], 200);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            "order_id" => "required",
        ]);
        // $order = Order::where(['id' => $request->order_id, 'status' => 'PLACED'])->first();
        $order = Order::where('id', $request->order_id)
            ->whereIn('status', ['DRAFT', 'PLACED'])
            ->first();

        if ($order) {
            // استخدام الـ Service لمعالجة الإلغاء
            $cancellationService = new \App\Services\OrderCancellationService();
            $result = $cancellationService->processOrderCancellation($order, 'customer');

            if ($result['success']) {
                $order->update(['status' => "CANCELLED"]);

                // إشعارات الإدارة
                $adminData = [
                    "title" => "Order #$order->order_code has been cancelled",
                    "message" => "Order #$order->order_code has been cancelled by " . auth()->user()->name,
                    "link" => "admin/order-details/" . $order->id,
                ];

                if ($result['total_refund_amount'] > 0 || $result['package_refunded']) {
                    $adminRefundMessage = "";

                    if ($result['total_refund_amount'] > 0) {
                        $adminRefundMessage .= " Amount {$result['total_refund_amount']} SAR has been refunded to customer's wallet.";
                    }

                    if ($result['package_refunded']) {
                        $adminRefundMessage .= " Package credit has been restored.";
                    }

                    $adminData["message"] .= $adminRefundMessage;
                }

                $this->sendNotifications($adminData, 'admin');

                // إعداد رسالة الاستجابة النهائية
                $responseMessage = $cancellationService->prepareCustomerSuccessMessage(
                    $result['total_refund_amount'],
                    $result['package_refunded']
                );

                return [
                    "status" => true,
                    "message" => $responseMessage,
                    "data" => [
                        "order" => $order,
                        "refunded_amount" => $result['total_refund_amount'],
                        "package_refunded" => $result['package_refunded'],
                        "refunded_transactions" => $result['refunded_transactions'],
                    ],
                ];
            } else {
                return [
                    "status" => false,
                    "message" => "Failed to cancel order: " . $result['error'],
                    "data" => [],
                ];
            }
        } else {
            return [
                "status" => false,
                "message" => "Order can't cancel at this stage",
                "data" => [],
            ];
        }
    }

    //Get User Orders
    public function myOrders(Request $request)
    {

        $orders = Order::where('user_id', $request->user()->id)
            ->latest('id')->paginate(20);
        return [
            "status" => true,
            "message" => "Here is your recent orders",
            "data" => [
                "orders" => $orders->items(),
                "pagination" => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ]
            ],
        ];
    }

    //Get User Orders
    public function getUnpaidOrders(Request $request)
    {

        $order = Order::where(['user_id' => auth()->user()->id, 'pay_status' => 'Unpaid'])->where('status', '!=', 'CANCELLED')
            ->where('grand_total', '>', 0)->first();
        return [
            "status" => true,
            "message" => "Here is your unpaid order",
            "data" => $order,
        ];
    }

    /**
     * Get user's unpaid and partial payment orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnpaidAndPartialOrders(Request $request)
    {
        $user = auth()->user();

        // Get orders with partial payment (any status except cancelled) OR unpaid delivered orders
        $orders = Order::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('pay_status', 'Partial')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('pay_status', 'Unpaid')
                            ->where('status', 'DELIVERED');
                    });
            })
            ->where('status', '!=', 'CANCELLED')
            ->where('grand_total', '>', 0)
            ->with(['orderItems.item', 'orderItems.addOns', 'deliveryAddress', 'vendor', 'driver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Format order items for each order
        $formattedOrders = $orders->getCollection()->map(function ($order) {
            $orderItems = $order->orderItems->map(function ($orderItem) {
                $item = $orderItem->item;
                $addOns = $orderItem->addOns->map(function ($addOn) {
                    return [
                        'id' => $addOn->id,
                        'name' => $addOn->name,
                        'name_ar' => $addOn->name_ar,
                        'price' => $addOn->pivot->price ?? $addOn->price,
                    ];
                });

                return [
                    'id' => $orderItem->id,
                    'item' => $item ? [
                        'id' => $item->id,
                        'name' => $item->name,
                        'name_ar' => $item->name_ar,
                        'description' => $item->description,
                        'description_ar' => $item->description_ar,
                        'image' => $item->image,
                    ] : null,
                    'service_type' => $orderItem->serviceType ? [
                        'id' => $orderItem->serviceType->id,
                        'name' => $orderItem->serviceType->name,
                        'name_ar' => $orderItem->serviceType->name_ar,
                    ] : null,
                    'quantity' => $orderItem->quantity,
                    'price' => $orderItem->price,
                    'total_price' => $orderItem->total_price,
                    'add_ons' => $addOns,
                ];
            });

            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'pickup_date' => $order->pickup_date,
                'pickup_time' => $order->pickup_time,
                'dropoff_date' => $order->dropoff_date,
                'dropoff_time' => $order->dropoff_time,
                'instructions' => $order->instructions,
                'pay_status' => $order->pay_status,
                'status' => $order->status,
                'status_display' => $order->status_display,
                'sub_total' => $order->sub_total,
                'delivery_fee' => $order->delivery_fee,
                'service_fee' => $order->service_fee ?? 0,
                'vat' => $order->vat,
                'grand_total' => $order->grand_total,
                'due_amount' => $order->due_amount ?? 0,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'order_items' => $orderItems,
                'delivery_address' => $order->deliveryAddress ? [
                    'id' => $order->deliveryAddress->id,
                    'address_type' => $order->deliveryAddress->address_type,
                    'street_no' => $order->deliveryAddress->street_no,
                    'house_no' => $order->deliveryAddress->house_no,
                    'area' => $order->deliveryAddress->area,
                    'building' => $order->deliveryAddress->building,
                    'appartment' => $order->deliveryAddress->appartment,
                    'floor' => $order->deliveryAddress->floor,
                    'lat' => $order->deliveryAddress->lat,
                    'lng' => $order->deliveryAddress->lng,
                    'door_password' => $order->deliveryAddress->door_password,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->name,
                    'name_ar' => $order->vendor->name_ar,
                    'phone' => $order->vendor->phone,
                ] : null,
                'driver' => $order->driver ? [
                    'id' => $order->driver->id,
                    'name' => $order->driver->name,
                    'phone' => $order->driver->phone,
                ] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Unpaid and partial payment orders retrieved successfully',
            'data' => [
                'orders' => $formattedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'has_more_pages' => $orders->hasMorePages(),
                ],
                'summary' => [
                    'total_orders' => $orders->total(),
                    'unpaid_orders' => $orders->where('pay_status', 'Unpaid')->count(),
                    'partial_orders' => $orders->where('pay_status', 'دفع_جزئي')->count(),
                    'pending_orders' => $orders->where('pay_status', 'Pending')->count(),
                ]
            ]
        ]);
    }

    public function getOrderDetails(Request $request)
    {
        Log::info('getOrderDetails.start');

        $user = $request->user();
        $orderId = (int)$request->input('order_id');

        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        $deliveryFee = $activePackage?->package?->delivery_fee ?? 0;

        Log::info('deliveryFee', ['deliveryFee' => $deliveryFee]);

        $order = Order::with(['orderItems.item', 'orderItems.addOns', 'deliveryAddress', 'user'])
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            Log::warning('Order not found or not owned by user', ['order_id' => $orderId, 'userId' => $user->id]);
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->append(['due_amount']);

        $isAr = ($request->string('language')->lower() === 'ar');

        $orderItemsArr = [];
        foreach ($order->orderItems as $orderItem) {
            $item = $orderItem->item;

            $itemData = [
                'id' => $item?->id,
                'name' => $item ? ($isAr ? $item->name_ar : $item->name) : null,
                'description' => $item ? ($isAr ? $item->description_ar : $item->description) : null,
                'image' => $item?->image,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->price,
                'service_id' => $orderItem->service_id,
            ];

            if ($orderItem->addOns && $orderItem->addOns->count() > 0) {
                $itemData['add_ons'] = $orderItem->addOns->map(function ($addOn) use ($isAr) {
                    return [
                        'id' => $addOn->id,
                        'name' => $isAr ? $addOn->name_ar : $addOn->name,
                        'price' => $addOn->pivot->price ?? $addOn->price,
                    ];
                })->values()->all();
            }

            $orderItemsArr[] = $itemData;
        }

        $serviceFee = (float)($order->service_fee ?? 0);
        $subTotal = (float)($order->sub_total ?? 0);
        $vat = (float)($order->vat ?? 0);
        $grandTotal = $subTotal + $deliveryFee + $serviceFee + $vat;

        $dueAmount = (float)($order->due_amount ?? 0);
        $partialDiscount = max(0, $grandTotal - $dueAmount);

        $payload = $order->toArray();
        $payload['order_items'] = $orderItemsArr;
        $payload['delivery_fee'] = (float)$deliveryFee;
        $payload['service_fee'] = $serviceFee;
        $payload['service_fee_applied'] = (bool)($order->service_fee_applied ?? false);
        $payload['grand_total'] = $grandTotal;
        $payload['partial_discount'] = $partialDiscount;

        if (!empty($payload['delivery_address']) && is_array($payload['delivery_address'])) {
            array_walk_recursive($payload['delivery_address'], function (&$v) {
                if ($v === null) {
                    $v = "";
                }
            });

            $expectedAddressKeys = [
                'id',
                'address_type',
                'street_no',
                'house_no',
                'area',
                'building',
                'appartment',
                'floor',
                'lat',
                'lng',
                'door_password',
                'user_id',
                'is_default',
                'basket_status',
                'basket_no',
                'deliver_image',
                'created_at'
            ];

            foreach ($expectedAddressKeys as $key) {
                if (!array_key_exists($key, $payload['delivery_address'])) {
                    $payload['delivery_address'][$key] = "";
                }
            }

            if (empty($payload['delivery_address']['street_no'])) {
                $payload['delivery_address']['street_no'] = "N/A";
            }
            if (empty($payload['delivery_address']['house_no'])) {
                $payload['delivery_address']['house_no'] = "N/A";
            }
        }

        Log::info('payload', ['payload' => $payload]);
        Log::info('order.details.ready', ['order_id' => $order->id]);

        return response()->json([
            'status' => true,
            'message' => 'Here is order details',
            'data' => $payload,
        ]);
    }

    public function applyPromoCode($code, $order_amount)
    {
        Log::info('applyPromoCode', ['code' => $code, 'order_amount' => $order_amount]);
        $promoCode = PromoCode::where('code', $code)->first();
        if ($promoCode) {
            $user_code = UserPromoCode::where(['code_id' => $promoCode->id, 'user_id' => auth()->user()->id])->first();
            if ($promoCode->expiry == 'COUNT') {
                $is_used = $promoCode->count == $user_code->count + 1 ? 1 : 0;
                $user_code->update(['count' => $user_code->count + 1, 'is_used' => $is_used]);
            } else {
                $user_code->update(['is_used' => 1]);
            }
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

        Log::info('applyPromoCode result', [
            'order_amount' => $order_amount,
            'taxable_base' => $taxableBase,
            'discountable_amount' => $discountableAmount,
            'discounted_amount' => $promoCode->discounted_amount,
            'max_order' => $promoCode->max_order,
            'promo_type' => $promoCode->promo_type
        ]);
        return $promoCode->discounted_amount;
    }


    public function checkDuplicateOrder($userId)
    {
        $currentTime = Carbon::now()->format('H:i');
        $order = Order::where(['user_id' => $userId])->whereRaw("DATE_FORMAT(created_at, '%H:%i') = ?", [$currentTime])->count();
        return $order;
    }

    /* ------------------ check if canPay by wallet or Package ------------------ */
    public function canPay(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $user = Auth::user();
        $order = Order::findOrFail($request->order_id);
        $amount = $order->grand_total;

        $canPayFromPackage = false;
        $canPayFromWallet = false;

        // فحص الباقة
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            // ->where('end_date', '>=', now())
            ->latest('start_date')
            ->first();

        if ($activePackage && $activePackage->remaining_credit >= $amount) {
            $canPayFromPackage = true;
        }

        // فحص المحفظة
        if ($user->wallet && $user->wallet->balance >= $amount) {
            $canPayFromWallet = true;
        }

        $shouldUseGateway = !$canPayFromPackage && !$canPayFromWallet;
        $preferredSource = $canPayFromPackage
            ? 'package'
            : ($canPayFromWallet ? 'wallet' : 'gateway');

        return response()->json([
            'status' => true,
            'message' => 'Payment source evaluated successfully',
            'data' => [
                'can_pay_from_package' => $canPayFromPackage,
                'can_pay_from_wallet' => $canPayFromWallet,
                'should_use_gateway' => $shouldUseGateway,
                'preferred_source' => $preferredSource,
                'required_amount' => $amount
            ]
        ]);
    }

    public function createOrderByClient(OrderRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (blank($user->phone)) {
            return response()->json(['status' => false, 'message' => "No phone found, please contact support", 'data' => []], 400);
        }

        $userId = $user->id;

        $address = UserAddress::where('user_id', $userId)->firstOrFail();
        Log::info('address', ['address' => $address]);
        $vendor = $address->vendor_id;
        // $vendor    = $this->getNearbyVendor($address->lat, $address->lng);
        Log::info('vendor', ['vendor' => $vendor]);
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => __('No vendor found in this area'),
            ], 422);
        }

        $priority = OrderPriority::findOrFail($request->order_priority_id);
        $leadHours = $priority->lead_time;
        $pickupAt = Carbon::now()->addHours($leadHours);
        $dropoffAt = $pickupAt->copy()->addDay();

        DB::beginTransaction();
        try {
            $existingDraft = Order::where('user_id', $userId)
                ->where('status', 'DRAFT')
                ->lockForUpdate()
                ->first();

            if ($existingDraft) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'You have a draft order, you can\'t create a new order before completing it or deleting it.',
                    'data' => $existingDraft,
                ], 409);
            }

            $order = Order::create([
                'order_code' => 'CB' . ((Order::latest()->first()->id ?? 0) + 1),
                'user_id' => $userId,
                'address_id' => $address->id,
                'pickup_date' => $pickupAt->toDateString(),
                'pickup_time' => $pickupAt->format('H:i'),
                'dropoff_date' => $dropoffAt->toDateString(),
                'dropoff_time' => $dropoffAt->format('H:i'),
                'instructions' => $request->instructions,
                'vendor_id' => null,
                'driver_id' => null,
                'delivery_fee' => 0,
                'pay_status' => 'Unpaid',
                'sorting' => 'client',
                'status' => 'DRAFT',
            ]);


            OrderTracking::firstOrCreate(
                ['order_id' => $order->id, 'status' => 'DRAFT'],
                ['order_id' => $order->id, 'status' => 'DRAFT']
            );

            foreach ($request->items as $itemData) {
                Log::info('Order creation debug', [
                    'item_id' => $itemData['item_id'],
                    'service_type_id' => $itemData['service_type_id'],
                    'order_priority_id' => $request->order_priority_id
                ]);

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
                        $orderItem->addOns()->attach($addOnId, [
                            'price' => $addOn->price,
                        ]);
                    }
                }
            }

            $subTotal = $order->items->sum('total_price');

            // حساب الضريبة أولاً
            $deliveryFee = $order->delivery_fee ?? 0;
            $baseAmountBeforeServiceFee = $subTotal + $deliveryFee;
            $vatAmount = round($baseAmountBeforeServiceFee * env('TAX_RATE', 0), 2);

            // حساب رسوم الخدمة بناءً على (المجموع الفرعي + الضريبة)
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
                'message' => 'Order created as draft. Complete payment to confirm it.',
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
                'message' => 'Failed to create order draft.',
            ], 500);
        }
    }

    public function updateOrderByClient(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'pickup_date' => 'nullable|string',
            'pickup_time' => 'nullable|string',
            'dropoff_date' => 'nullable|string',
            'dropoff_time' => 'nullable|string',
            'instructions' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required_with:items|exists:items,id',
            'items.*.service_type_id' => 'required_with:items|exists:service_types,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.add_on_ids' => 'nullable|array',
            'items.*.add_on_ids.*' => 'exists:add_ons,id',
        ]);

        $user = auth()->user();
        $userId = $user->id;

        // Find the order and verify ownership
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Check if order can be updated (only DRAFT orders should be updatable)
        if ($order->status !== 'DRAFT') {
            return response()->json([
                'status' => false,
                'message' => 'Only draft orders can be updated.',
            ], 422);
        }

        // Get user address and validate vendor (only if updating items with data)
        $address = UserAddress::where('user_id', $userId)->firstOrFail();
        $vendor = null;

        if ($request->has('items') && is_array($request->items) && !empty($request->items)) {
            $vendor = $this->getAreaVendor($address->lat, $address->lng);
            if (!$vendor) {
                return response()->json([
                    'status' => false,
                    'message' => __('No vendor found in this area'),
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Prepare update data array
            $updateData = [];

            // Update dates and times if provided
            if ($request->has('pickup_date') && $request->has('pickup_time')) {
                // Extract start time from range (e.g., "11:00 - 12:00" -> "11:00")
                $pickupTime = explode(' - ', $request->pickup_time)[0];
                $pickupAt = Carbon::parse($request->pickup_date . ' ' . $pickupTime);
                $updateData['pickup_date'] = $pickupAt->toDateString();
                $updateData['pickup_time'] = $request->pickup_time; // Store full range
            }

            if ($request->has('dropoff_date') && $request->has('dropoff_time')) {
                // Extract start time from range (e.g., "10:00 - 11:00" -> "10:00")
                $dropoffTime = explode(' - ', $request->dropoff_time)[0];
                $dropoffAt = Carbon::parse($request->dropoff_date . ' ' . $dropoffTime);
                $updateData['dropoff_date'] = $dropoffAt->toDateString();
                $updateData['dropoff_time'] = $request->dropoff_time; // Store full range
            }

            // Update instructions if provided
            if ($request->has('instructions')) {
                $updateData['instructions'] = $request->instructions;
            }

            // Update vendor if provided
            if ($vendor) {
                $updateData['vendor_id'] = $vendor->id;
            }

            // Update order basic information
            if (!empty($updateData)) {
                $order->update($updateData);
            }

            // Update items if 'items' key is present in request - always replace existing items
            if ($request->has('items')) {
                // Delete all existing items first
                $order->items()->delete();

                // Add new items if array is not empty
                if (is_array($request->items) && !empty($request->items)) {
                    foreach ($request->items as $itemData) {
                        $itemId = $itemData['item_id'];
                        $serviceTypeId = $itemData['service_type_id'];
                        $quantity = (int)$itemData['quantity'];
                        $addOnIds = $itemData['add_on_ids'] ?? [];

                        Log::info('Order update - creating new item', [
                            'item_id' => $itemId,
                            'service_type_id' => $serviceTypeId,
                            'quantity' => $quantity,
                        ]);

                        $pivot = DB::table('item_service_type')
                            ->where('item_id', $itemId)
                            ->where('service_type_id', $serviceTypeId)
                            ->first(['price', 'discount_price']);

                        if (!$pivot) {
                            throw new \Exception("Item service type not found for item_id: {$itemId}, service_type_id: {$serviceTypeId}");
                        }

                        $unitPrice = $pivot->discount_price !== null
                            ? (float)$pivot->discount_price
                            : (float)$pivot->price;

                        // Create new item
                        $baseTotal = $unitPrice * $quantity;
                        $addonsTotal = 0;

                        if (!empty($addOnIds)) {
                            $addOns = AddOn::whereIn('id', $addOnIds)->get();
                            foreach ($addOns as $addOn) {
                                $addonsTotal += (float)$addOn->price;
                            }
                            $addonsTotal *= $quantity;
                        }

                        $totalPrice = $baseTotal + $addonsTotal;

                        $orderItem = $order->items()->create([
                            'item_id' => $itemId,
                            'service_type_id' => $serviceTypeId,
                            'price' => $unitPrice,
                            'quantity' => $quantity,
                            'total_price' => $totalPrice,
                        ]);

                        if (!empty($addOnIds)) {
                            foreach ($addOnIds as $addOnId) {
                                $addOn = AddOn::find($addOnId);
                                $orderItem->addOns()->attach($addOnId, [
                                    'price' => $addOn->price,
                                ]);
                            }
                        }
                    }
                }

                // Recalculate totals for all items
                $subTotal = $order->items()->sum('total_price');

                // حساب الضريبة أولاً
                $deliveryFee = $order->delivery_fee ?? 0;
                $baseAmountBeforeServiceFee = $subTotal + $deliveryFee;
                $vatAmount = round($baseAmountBeforeServiceFee * env('TAX_RATE', 0), 2);

                // حساب رسوم الخدمة بناءً على (المجموع الفرعي + الضريبة)
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
            }

            // Update order tracking
            OrderTracking::firstOrCreate(
                ['order_id' => $order->id, 'status' => 'DRAFT'],
                ['order_id' => $order->id, 'status' => 'DRAFT']
            );

            DB::commit();

            // Reload the order with relationships
            $order->load('items.serviceType', 'items.addOns', 'items.item.category');

            // Get service fee data for response
            $serviceFeeData = $this->calculateServiceFee($order->sub_total, $order->vat ?? 0);

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully.',
                'data' => array_merge($order->toArray(), [
                    'price' => [
                        'sub_total' => $order->sub_total,
                        'service_fee' => $order->service_fee,
                        'service_fee_applied' => $order->service_fee_applied ?? $serviceFeeData['service_fee_applied'],
                        'service_fee_reason' => $serviceFeeData['reason'],
                        'delivery_fee' => $order->delivery_fee,
                        'vat_amount' => $order->vat_amount,
                        'grand_total' => $order->grand_total,
                    ],
                ]),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error("Order update failed: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order, '. $e->getMessage(),
            ], 500);
        }
    }

    public function showOrderByClient(Order $order): JsonResponse
    {
        // إعادة تحميل بنفس العلاقات
        $order->load([
            'items.serviceType',
            'items.addOns',
            'items.item.category',
            'priority'
        ]);

        $price = [
            'sub_total' => $order->sub_total,
            'delivery_fee' => $order->delivery_fee,
            'vat_amount' => $order->vat_amount,
            'grand_total' => $order->grand_total,
        ];

        return response()->json([
            'data' => array_merge($order->toArray(), ['price' => $price]),
        ]);
    }

    public function updateOrderByVendor(Request $request): JsonResponse
    {
        // Debug: تسجيل البيانات المستلمة
        Log::info('updateOrderByVendor request data:', $request->all());
        Log::info('updateOrderByVendor headers:', $request->headers->all());

        $rawContent = $request->getContent();
        Log::info('updateOrderByVendor raw content:', ['content' => $rawContent]);

        $jsonDecoded = json_decode($rawContent, true);
        Log::info('updateOrderByVendor JSON decoded:', ['decoded' => $jsonDecoded, 'json_error' => json_last_error_msg()]);

        // محاولة استخراج البيانات من المحتوى الخام إذا لم تكن متاحة
        if (empty($request->all()) && $rawContent) {
            $jsonData = json_decode($rawContent, true);
            if ($jsonData && json_last_error() === JSON_ERROR_NONE) {
                $request->merge($jsonData);
                Log::info('Data merged from raw content:', ['merged_data' => $jsonData]);
            } else {
                Log::error('Failed to parse JSON:', [
                    'content' => $rawContent,
                    'json_error' => json_last_error_msg()
                ]);
            }
        }

        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'order_priority_id' => 'sometimes|exists:order_priorities,id',
                'instructions' => 'nullable|string',
                'items' => 'sometimes|array',
                'items.*.item_id' => 'required_with:items|exists:items,id',
                'items.*.service_type_id' => 'required_with:items|exists:service_types,id',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'items.*.add_on_ids' => 'array',
                'items.*.add_on_ids.*' => 'exists:add_ons,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'raw_content' => $request->getContent()
            ]);
            throw $e;
        }

        $vendor = auth('vendors')->user();
        $order = Order::findOrFail($request->order_id);

        // التأكد من أن الطلب يخص هذا البائع
        if ($order->vendor_id !== $vendor->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this order.',
            ], 403);
        }

        // التحقق من حالة الطلب - يجب أن يكون ARRIVED للفرز
        if ($order->status !== 'ARRIVED') {
            return response()->json([
                'status' => false,
                'message' => 'Order must be in ARRIVED status to be processed.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // إذا لم يتم إرسال عناصر، فقط تحديث الحالة إلى PROCESSING
            if (empty($request->items)) {
                $order->update(['status' => 'PROCESSING']);

                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'PROCESSING',
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Order status updated to PROCESSING.',
                    'data' => $order->fresh(),
                ], 200);
            }

            // Update or create items according to the request
            foreach ($request->items as $itemData) {
                $pivot = DB::table('item_service_type')
                    ->where('item_id', $itemData['item_id'])
                    ->where('service_type_id', $itemData['service_type_id'])
                    // ->where('order_priority_id', $request->order_priority_id)
                    ->first(['price', 'discount_price']);

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

                //search for an existing item with the same item_id and service_type_id
                $existingItem = $order->items()
                    ->where('item_id', $itemData['item_id'])
                    ->where('service_type_id', $itemData['service_type_id'])
                    ->first();

                if ($existingItem) {
                    // update the existing item
                    $existingItem->update([
                        'price' => $unitPrice,
                        'quantity' => $quantity,
                        'total_price' => $totalPrice,
                    ]);

                    // update the addons
                    $existingItem->addOns()->detach();
                    if (!empty($itemData['add_on_ids'])) {
                        foreach ($itemData['add_on_ids'] as $addOnId) {
                            $addOn = AddOn::find($addOnId);
                            $existingItem->addOns()->attach($addOnId, [
                                'price' => $addOn->price,
                            ]);
                        }
                    }
                } else {
                    // create a new item
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
                            $orderItem->addOns()->attach($addOnId, [
                                'price' => $addOn->price,
                            ]);
                        }
                    }
                }
            }

            // recalculate the totals
            $subTotal = $order->items->sum('total_price');

            // حساب الضريبة أولاً
            $deliveryFee = $order->delivery_fee ?? 0;
            $baseAmountBeforeServiceFee = $subTotal + $deliveryFee;
            $vatAmount = round($baseAmountBeforeServiceFee * env('TAX_RATE', 0), 2);

            // حساب رسوم الخدمة بناءً على (المجموع الفرعي + الضريبة)
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
                'status' => 'PROCESSING', // تحديث حالة الطلب إلى PROCESSING
            ]);

            // إضافة تتبع الحالة الجديدة
            OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'PROCESSING',
            ]);

            DB::commit();

            $order->load('items.serviceType', 'items.addOns', 'items.item.category');

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully.',
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
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error("Order update failed: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update order.',
            ], 500);
        }
    }
}
