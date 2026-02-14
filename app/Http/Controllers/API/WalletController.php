<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\WalletSetting;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;


class WalletController extends Controller
{
    // 🟢 عرض رصيد المحفظة
    public function getBalance(Request $request)
    {
        $user = Auth::user();

        // البحث أو إنشاء محفظة
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        // جلب رصيد الباقة النشطة (إن وُجدت)
        $packageCredit = $user->userPackages()
            ->where('is_active', true)
            // ->where('end_date', '>=', now())
            ->latest('start_date')
            ->value('remaining_credit') ?? 0;

        // الرصيد الموحد
        $totalBalance = $wallet->balance + $packageCredit;

        return response()->json([
            'status' => true,
            'message' => 'Unified balance retrieved successfully',
            'data' => [
                // 'wallet_balance' => $wallet->balance,
                // 'package_balance' => $packageCredit,
                'total_balance' => $totalBalance
            ]
        ]);
    }
    // 🟢 شحن المحفظة
    public function chargeFromSDK(Request $request)
    {
        $data = $request->input('payment_response'); // ✅ قراءة البيانات من داخل payment_response

        if (
            !isset($data['InvoiceStatus']) ||
            $data['InvoiceStatus'] !== 'Paid' ||
            empty($data['InvoiceTransactions'][0]) ||
            $data['InvoiceTransactions'][0]['TransactionStatus'] !== 'Succss'
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not successful'
            ], 422);
        }

        $user = Auth::user();
        $invoice = $data['InvoiceTransactions'][0];

        $amount = (float) $invoice['TransactionValue']; // ✅ انتبه spelling TransactionValue وليس TransationValue
        $transactionId = 'WAT-' . now()->format('YmdHis') . '-' . Str::random(4);
        $trackId = $invoice['TrackId'] ?? ''; // قد يكون موجود أو لا
        $paymentGateway = $invoice['PaymentGateway'];

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $existing = WalletTransaction::where('description', 'LIKE', "%{$transactionId}%")->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'This transaction was already processed'
            ], 409);
        }

        $settings = WalletSetting::first();
        $new_balance = $wallet->balance + $amount;

        if ($settings && $settings->max_balance !== null && $new_balance > $settings->max_balance) {
            return response()->json([
                'status' => false,
                'message' => "Cannot charge wallet: balance would exceed maximum allowed ({$settings->max_balance} SR)."
            ], 422);
        }

        $wallet->increment('balance', $amount);

        $wallet->transactions()->create([
            'transaction_id' => $transactionId,
            'type' => 'credit',
            'amount' => $amount,
            'source' => 'user',
            'description' => "SDK Payment: TxID {$transactionId} | Track {$trackId} | Gateway: {$paymentGateway}",
            'payment_response' => $data
        ]);

        Controller::sendNotifications([
            "title" => "Wallet Charged",
            "title_ar" => "تم شحن المحفظة",
            "message" => "Your wallet has been credited with {$amount} SR",
            "message_ar" => "تم إضافة {$amount} ريال إلى محفظتك",
            "user" => $user
        ], "user");

        return response()->json([
            'status' => true,
            'message' => 'Wallet charged successfully',
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    //شحن المحفظة من قبل الادارة
    public function manualCharge(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string'
        ]);

        $user = User::findOrFail($request->user_id);

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );


        $settings = WalletSetting::first();

        $new_balance = $wallet->balance + $request->amount;

        if ($settings && $settings->max_balance !== null && $new_balance > $settings->max_balance) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot charge wallet: maximum balance exceeded.'
            ], 422);
        }

        $transactionId = 'ADM-' . now()->format('YmdHis') . '-' . Str::random(4);

        $wallet->increment('balance', $request->amount);

        $wallet->transactions()->create([
            'transaction_id' => $transactionId,
            'type' => 'credit',
            'amount' => $request->amount,
            'source' => 'admin',
            'description' => $request->note ?? 'Manual top-up by admin',
            'payment_response' => null,
         ]);

        Controller::sendNotifications([
            "title" => "Wallet Updated by Admin",
            "title_ar" => "تم شحن محفظتك يدويًا",
            "message" => "Your wallet has been manually credited with {$request->amount} SR",
            "message_ar" => "تم إضافة {$request->amount} ريال إلى محفظتك من قِبل الإدارة",
            "user" => $user
        ], "user");

        return response()->json([
            'status' => true,
            'message' => 'Wallet manually charged successfully',
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    // 🔴 خصم تلقائي عند الطلب
    public function deduct(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'order_id' => 'nullable|exists:orders,id'
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $orderId = $request->order_id;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $remaining = $amount;

        // 🟢 محاولة الخصم من الباقة النشطة
        $activePackage = $user->userPackages()
            ->where('is_active', true)
             ->latest('start_date')
            ->first();

        if ($activePackage && $activePackage->remaining_credit > 0) {
            $deductFromPackage = min($activePackage->remaining_credit, $remaining);

            $activePackage->decrement('remaining_credit', $deductFromPackage);

            // 📝 تسجيل العملية كخصم من الباقة
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_package_id' => $activePackage->id,
                'type' => 'debit',
                'amount' => $deductFromPackage,
                'source' => 'package',
                'description' => $request->description,
                'related_order_id' => $orderId,
                'payment_response' => null
            ]);

            $remaining -= $deductFromPackage;
        }

        // 🟡 إذا تبقى شيء نخصمه من المحفظة
        if ($remaining > 0) {
            if ($wallet->balance < $remaining) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient wallet balance'
                ], 422);
            }

            $settings = WalletSetting::first();
            $new_balance = $wallet->balance - $remaining;

            if ($settings && $settings->min_balance !== null && $new_balance < $settings->min_balance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot deduct: balance would fall below minimum allowed balance.'
                ], 422);
            }

            $wallet->decrement('balance', $remaining);

            // 📝 تسجيل العملية كخصم من المحفظة
            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $remaining,
                'source' => 'wallet',
                'description' => $request->description,
                'related_order_id' => $orderId,
                'payment_response' => null
            ]);
        }

        // إرسال إشعار موحد
        Controller::sendNotifications([
            "title" => "Balance Deduction",
            "title_ar" => "تم خصم من الرصيد",
            "message" => "An amount of {$amount} SR has been deducted from your balance.",
            "message_ar" => "تم خصم {$amount} ريال من رصيدك.",
            "user" => $user
        ], "user");

        return response()->json([
            'status' => true,
            'message' => 'Amount deducted successfully',
            'data' => [
                // 'balance' => $wallet->balance // رصيد المحفظة فقط، يمكن تعديله لعرض الموحد إن أردت
                'balance' => $wallet->balance + ($activePackage->remaining_credit ?? 0)

            ]
        ]);
    }

    // 🟡 سجل العمليات
    public function getTransactions(Request $request)
    {
        $user = auth()->user();

        if (!$user->wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found'
            ], 404);
        }

        $transactions = $user->wallet->transactions()
            ->with('userPackage') // تأكد أن العلاقة موجودة في الموديل
            ->latest()
            ->get()
            ->map(function ($tx) {
                return [
                    'transaction_id' => $tx->transaction_id,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'vat_amount' => $tx->vat_amount,
                    'total_amount' => $tx->amount + $tx->vat_amount,
                    'source' => $tx->source, // wallet, package, admin, etc
                    // 'package_name' => $tx->userPackage?->package?->name ?? null,
                    'description' => $tx->description,
                    'related_order_id' => $tx->related_order_id,
                    'date' => \Carbon\Carbon::parse($tx->created_at)->toDateTimeString(),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $transactions
        ]);
    }

}
