<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\PackageTransaction;
use App\Models\UserPackage;
use App\Services\VoucherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::select(
            'id',
            'name',
            'name_en',
            'vat',
            'price',
            'total_price',
            'cashback_amount',
            'delivery_fee',
            // 'duration_days',
            'has_priority'
        )->get();

        return response()->json([
            'status' => true,
            'message' => 'Available packages retrieved successfully',
            'message_ar' => 'تم جلب الباقات المتاحة بنجاح',
            'data' => $packages
        ]);
    }

    public function purchasePackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_response' => 'array|min:0',
        ]);

        $user = Auth::user();
        $package = Package::findOrFail($request->package_id);

        $price = $package->price;
        $vatAmount = $package->vat;
        $AmountWithVat = $price + $vatAmount;
        $cashback = $package->cashback_amount;
        $credit = $price + $cashback;
        Log::info('=================================');
        Log::info('package' . $package);
        Log::info('vatAmount' . $vatAmount);
        Log::info('=================================');


        $paymentResponse = $request->payment_response;
        $paid = false;

        // ✅ الدفع عبر SDK فقط
        if (isset($paymentResponse['InvoiceStatus']) && $paymentResponse['InvoiceStatus'] === 'Paid') {
            $paid = true;

            $transactionId = 'PAK-' . now()->format('YmdHis') . '-' . Str::random(4);

            $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0])
                ->transactions()
                ->create([
                    'type' => 'credit',
                    'amount' => $price,
                    'vat_amount' => $vatAmount,
                    'source' => 'sdk',
                    'description' => 'Package purchased via SDK: ' . $package->name,
                    'payment_response' => $paymentResponse,
                    'transaction_id' => $transactionId,
                ]);
        }

        if (! $paid) {
            return response()->json([
                'status' => false,
                'message' => 'Only SDK payments are allowed for purchasing packages.',
                'message_ar' => 'يسمح بشراء الباقات عبر بوابة الدفع فقط.'
            ], 422);
        }

        $existingPackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->first();

        $startDate = now();
        $endDate = null;

        // 🧠 لا توجد باقة نشطة
        if (! $existingPackage) {
            $userPackage = UserPackage::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'total_credit' => $credit,
                'vat_amount' => $vatAmount,
                'remaining_credit' => $credit,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => true,
                'auto_renew' => false,
                'payment_response' => $paymentResponse,
            ]);
        }

        // 🧠 نفس الباقة → تجميع الرصيد
        elseif ($existingPackage->package_id === $package->id) {
            $existingPackage->increment('total_credit', $credit);
            $existingPackage->increment('remaining_credit', $credit);
            $existingPackage->update(['payment_response' => $paymentResponse]);

            $userPackage = $existingPackage;
        }

        // 🧠 باقة مختلفة → ترقية ودمج الرصيد مباشرة
        else {
            $transferred = $existingPackage->remaining_credit;

            $existingPackage->update([
                'is_active' => false,
                'remaining_credit' => 0,
            ]);

            $userPackage = UserPackage::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'total_credit' => $credit + $transferred,
                'vat_amount' => $vatAmount,
                'remaining_credit' => $credit + $transferred,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => true,
                'auto_renew' => false,
                'payment_response' => $paymentResponse,
            ]);
        }

        // 🟢 منح القسائم
        // $voucherCount = $package->voucher_count ?? 0;
        // if ($voucherCount > 0) {
        //     (new VoucherService)->grantVouchers($user, $voucherCount, $package->id, 'Voucher for package: ' . $package->name);

        //     Controller::sendNotifications([
        //         "title" => "Vouchers Granted",
        //         "title_ar" => "تم منحك قسائم مجانية",
        //         "message" => "You have received $voucherCount vouchers with your package ({$package->name}).",
        //         "message_ar" => "تم منحك $voucherCount قسيمة مجانية مع باقة ({$package->name}).",
        //         "user" => $user
        //     ], "user");
        // }

        // سجل المعاملة
        $userPackage->transactions()->create([
            'type' => 'credit',
            'amount' => $credit,
            'vat_amount' => $vatAmount,
            'description' => 'اشتراك في الباقة',
        ]);

        // إشعار المستخدم
        Controller::sendNotifications([
            "title" => "Package Subscribed",
            "title_ar" => "تم الاشتراك في الباقة",
            "message" => "You have successfully subscribed to {$package->name} with balance {$credit} SR",
            "message_ar" => "تم الاشتراك في {$package->name} بنجاح ورصيد {$credit} ريال",
            "user" => $user
        ], "user");

        return response()->json([
            'status' => true,
            'message' => 'Package purchased successfully',
            'message_ar' => 'تم شراء الباقة بنجاح',
            'data' => [
                'package' => $package->name,
                'credit' => $userPackage->remaining_credit,
                'valid_until' => $endDate ? $endDate->toDateString() : null,
            ]
        ]);
    }

    public function getMyPackages()
    {
        $user = auth()->user();

        $userPackage = $user->userPackages()
            ->where('is_active', true)
            ->where('remaining_credit', '>', 0)
            ->latest('start_date')
            ->first();

        if (!$userPackage) {
            return response()->json([
                'status' => false,
                'message' => 'No active package found.',
                'message_ar' => 'لم يتم العثور على باقة نشطة',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Active package retrieved successfully.',
            'message_ar' => 'تم جلب الباقة النشطة بنجاح',
            'data' => [
                'package_name' => $userPackage->package->name,
                'package_name_en' => $userPackage->package->name_en,
                'total_credit' => $userPackage->total_credit,
                'remaining_credit' => $userPackage->remaining_credit,
                'start_date' => $userPackage->start_date
            ]
        ]);
    }

    // HOLD ON THIS FUNCTION
    // public function toggleAutoRenew(Request $request)
    // {
    //     $request->validate([
    //         'auto_renew' => 'required|boolean'
    //     ]);

    //     $user = auth()->user();

    //     $activePackage = $user->userPackages()
    //         ->where('is_active', true)
    //         ->where('end_date', '>=', now())
    //         ->latest('start_date')
    //         ->first();

    //     if (!$activePackage) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No active package found.'
    //         ], 404);
    //     }

    //     $activePackage->update([
    //         'auto_renew' => $request->auto_renew
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => $request->auto_renew
    //             ? 'Auto-renew has been enabled.'
    //             : 'Auto-renew has been disabled.'
    //     ]);
    // }

    public function upgradePackage(Request $request)
    {
        $request->validate([
            'package_id'       => 'required|exists:packages,id',
            'payment_response' => 'array|min:0', // الدفع فقط عبر SDK
        ]);

        $user            = auth()->user();
        $newPackage      = Package::findOrFail($request->package_id);
        $price           = $newPackage->price;
        $cashback        = $newPackage->cashback_amount;
        $total           = $price + $cashback;
        $paymentResponse = $request->payment_response;
        $paid            = false;

        // 1️⃣ جلب الباقة القديمة النشطة
        $oldPackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->first();

        // ❌ إذا المستخدم يطلب نفس الباقة الحالية
        if ($oldPackage && $oldPackage->package_id == $newPackage->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You are already subscribed to this package.',
                'message_ar' => 'أنت مشترك بالفعل في هذه الباقة حالياً.'
            ], 422);
        }

        // 2️⃣ ترحيل الرصيد المتبقي إلى المحفظة (إن وُجد)
        if ($oldPackage && $oldPackage->remaining_credit > 0) {
            $transferAmount = $oldPackage->remaining_credit;

            $wallet = $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $wallet->increment('balance', $transferAmount);

            $wallet->transactions()->create([
                'type' => 'credit',
                'amount' => $transferAmount,
                'source' => 'package',
                'description' => 'Transfer of remaining package balance before upgrade',
                'user_package_id' => $oldPackage->id,
            ]);
        }

        // 3️⃣ الدفع من SDK فقط (يجب أن يكون payment_response صالح)
        if (!isset($paymentResponse['InvoiceStatus']) || $paymentResponse['InvoiceStatus'] !== 'Paid') {
            return response()->json([
                'status'  => false,
                'message' => 'Only SDK payments are allowed for package upgrade.',
                'message_ar' => 'يسمح بترقية الباقة عبر بوابة الدفع فقط.'
            ], 422);
        }

        $paid = true;

        // تسجيل العملية في wallet_transactions كمصدرها SDK
        $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0])
            ->transactions()
            ->create([
                'type' => 'credit',
                'amount' => $price,
                'source' => 'sdk',
                'description' => 'Upgrade package via SDK: ' . $newPackage->name,
                'payment_response' => $paymentResponse
            ]);

        // 4️⃣ إلغاء الباقة القديمة وتصفير رصيدها
        if ($oldPackage) {
            $oldPackage->update([
                'is_active'        => false,
                'remaining_credit' => 0,
            ]);
        }

        // 5️⃣ إنشاء الاشتراك الجديد
        $startDate = now();
        $endDate = null;

        $newUserPackage = $user->userPackages()->create([
            'package_id'       => $newPackage->id,
            'total_credit'     => $total,
            'remaining_credit' => $total,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'is_active'        => true,
            'auto_renew'       => false,
            'payment_response' => $paymentResponse,
        ]);

        // 6️⃣ منح القسائم (إن وجدت)
        // $voucherCount = $newPackage->voucher_count ?? 0;
        // if ($voucherCount > 0) {
        //     (new VoucherService)->grantVouchers($user, $voucherCount, $newPackage->id, 'Voucher for package: ' . $newPackage->name);

        //     Controller::sendNotifications([
        //         "title"      => "Vouchers Granted",
        //         "title_ar"   => "تم منحك قسائم مجانية",
        //         "message"    => "You have received $voucherCount vouchers with your package ({$newPackage->name}). You can use them on your next orders.",
        //         "message_ar" => "تم منحك $voucherCount قسيمة مجانية مع باقة ({$newPackage->name}). يمكنك استخدامها في طلباتك القادمة.",
        //         "user"       => $user
        //     ], "user");
        // }

        // 7️⃣ تسجيل العملية في package_transactions
        $newUserPackage->transactions()->create([
            'type'        => 'credit',
            'amount'      => $total,
            'description' => 'ترقية الباقة',
        ]);

        // 8️⃣ إشعار المستخدم
        Controller::sendNotifications([
            'title'      => 'Package upgraded successfully',
            'title_ar'   => 'تمت ترقية باقتك بنجاح',
            'message'    => "You have been upgraded to {$newPackage->name} with {$total} SR credit.",
            'message_ar' => "تمت ترقية باقتك إلى {$newPackage->name} برصيد {$total} ريال.",
            'user'       => $user
        ], 'user');

        return response()->json([
            'status'  => true,
            'message' => 'Package upgraded successfully',
            'message_ar' => 'تمت ترقية باقتك بنجاح',
            'data'    => [
                'package'      => $newPackage->name,
                'credit'       => $total,
                'valid_until' => $endDate ? $endDate->toDateString() : null,
            ]
        ]);
    }

    public function getCurrentPackage(Request $request)
    {
        $user = auth()->user();

        $activePackage = $user->userPackages()
            ->with('package')
            ->where('is_active', true)
            ->where('remaining_credit', '>', 0)
            ->latest('start_date')
            ->first();

        if (!$activePackage) {
            return response()->json([
                'status' => false,
                'message' => 'No active package found.',
                'message_ar' => 'لم يتم العثور على باقة نشطة',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'package_name' => $activePackage->package->name ?? '',
                'package_name_en' => $activePackage->package->name_en ?? '',
                'total_credit' => $activePackage->total_credit,
                'remaining_credit' => $activePackage->remaining_credit,
                'start_date' => \Carbon\Carbon::parse($activePackage->start_date)->toDateString(),
            ]
        ]);
    }

    public function getHistory(Request $request)
    {
        $user = auth()->user();

        $history = $user->userPackages()
            ->with('package')
            ->where('is_active', false)
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($pkg) {
                return [
                    'package_name' => $pkg->package->name ?? '',
                    'package_name_en' => $pkg->package->name_en ?? '',
                    'total_credit' => $pkg->total_credit,
                    'used_credit' => $pkg->total_credit - $pkg->remaining_credit,
                    'remaining_credit' => $pkg->remaining_credit,
                    'start_date' => \Carbon\Carbon::parse($pkg->start_date)->toDateString(),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $history
        ]);
    }
}
