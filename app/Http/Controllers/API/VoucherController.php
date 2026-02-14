<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * القسائم المملوكة للمستخدم (لم تُهدى بعد)
     */
    public function myVouchers(Request $request)
    {
        $user = $request->user();

        $vouchers = UserVoucher::with('voucher')
            ->where('user_id', $user->id)
            ->whereNull('gifted_to_phone')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * القسائم التي أُهديت للمستخدم الحالي عبر رقم الجوال
     */
    public function giftedVouchers(Request $request)
    {
        $user = $request->user();

        $vouchers = UserVoucher::with('voucher')
            ->where('gifted_to_phone', $user->phone)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * إهداء قسيمة لصديق عبر رقم الجوال
     */
    public function giftVoucher(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|exists:user_vouchers,code',
            'gift_to_phone' => 'required|string|min:8|max:20',
        ]);

        $user = auth()->user();

        // تحقق أن القسيمة مملوكة للمستخدم ولم تُهدى بعد
        $userVoucher = UserVoucher::where('code', $request->code)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('gifted_to_phone', $user->phone);
            })
            ->whereNull('gifted_to_phone')
            ->where('is_active', true)
            ->first();

        if (!$userVoucher) {
            return response()->json([
                'status'  => false,
                'message' => 'No available voucher to gift or already gifted.'
            ], 422);
        }

        // تنفيذ منطق الإهداء (استخدام Service لو متبع تنظيم أفضل)
        $recipient = User::where('phone', $request->gift_to_phone)->first();

        $userVoucher->update([
            'gifted_to_user_id' => $recipient?->id,
            'gifted_to_phone'   => $request->gift_to_phone,
            'gifted_at'         => now(),
            // لاحظ لم تعد مملوكة للمستخدم الأصلي الآن في الاستهلاك!
        ]);

        // يمكنك إرسال إشعار لصديقك هنا إذا كان مستخدمًا في التطبيق

        return response()->json([
            'status'  => true,
            'message' => 'Voucher gifted successfully.'
        ]);
    }

    /**
     * معاينة تطبيق القسيمة بدون حفظ أي تغيير على قاعدة البيانات
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function applyVoucher(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'voucher_code' => 'required|string',
        ]);

        $user  = auth()->user();
        $order = Order::findOrFail($request->order_id);

        // 1️⃣ جلب القسيمة النشطة
        $userVoucher = UserVoucher::where('code', $request->voucher_code)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $userVoucher) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or inactive voucher code.',
            ], 422);
        }

        // 2️⃣ التأكد من وجود رصيد متبقٍ
        $remaining = $userVoucher->remaining_amount;
        if ($remaining <= 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Voucher has no remaining balance.',
            ], 422);
        }

        // 3️⃣ حساب مبلغ الخصم (الأصغر بين الرصيد وقيمة الطلب)
        $discount = min($remaining, $order->sub_total);

        // 4️⃣ إرجاع البيانات دون أي تعديل في الـ DB
        return response()->json([
            'status'  => true,
            'message' => 'Voucher applied successfully.',
            'data'    => [
                'voucher_code'       => $userVoucher->code,
                'discount_amount'    => $discount,
                'order_total_before' => $order->sub_total,
                'order_total_after'  => round($order->sub_total - $discount, 2),
                'remaining_amount'   => $remaining,
            ],
        ]);
    } 
}
