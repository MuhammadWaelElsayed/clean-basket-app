<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Support\Str;

class VoucherService
{
    /**
     * يمنح قسائم للمستخدم بناءً على عدد القسائم
     */
    public function grantVouchers(User $user, $voucherCount, int $packageId, $note = null)
    {
        if ($voucherCount <= 0) {
            return;
        }

        // 🧠 حدد القسيمة المناسبة حسب رقم الباقة
        $voucherMap = [
            2 => 2, // package_id => voucher_id
            3 => 3,
        ];

        $voucherId = $voucherMap[$packageId] ?? null;

        if (!$voucherId) {
            return; // لا يوجد قسيمة مرتبطة بهذه الباقة
        }
        $voucher = Voucher::find($voucherId);


        if (!$voucher) {
            return; // تحقق احتياطيًا
        }

        for ($i = 0; $i < $voucherCount; $i++) {
            UserVoucher::create([
                'user_id' => $user->id,
                'voucher_id' => $voucher->id,
                'code' => strtoupper(Str::random(10)),
                'remaining_uses' => 1,
                'assigned_at' => now(),
                'expired_at' => null,
                'is_active' => true,
                'gifted_to_user_id' => null,
                'gifted_to_phone' => null,
                'gifted_at' => null,
            ]);
        }
    }

    /**
     *   يمنح قسيمة لمستخدم آخر عبر رقم الجوال
     */
    public function giftVoucher(User $user, $voucherId, $toPhone)
    {
        $userVoucher = UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucherId)
            ->whereNull('gifted_to_phone')
            ->where('is_active', true)
            ->first();

        if (!$userVoucher) {
            return ['status' => false, 'message' => 'No available voucher to gift.'];
        }

        // ابحث عن مستخدم بالهاتف إذا موجود (اختياري)
        $recipient = User::where('phone', $toPhone)->first();

        $userVoucher->update([
            'gifted_to_user_id' => $recipient?->id,
            'gifted_to_phone' => $toPhone,
            'gifted_at' => now(),
        ]);

        // أرسل إشعار إذا أردت...

        return ['status' => true, 'message' => 'Voucher gifted successfully.'];
    }

    /**
     * يستهلك القسيمة بناءً على الكود المدخل من قبل المستخدم
     */
    public function consumeVoucherByCode(User $user, string $code, float $discount, ?Order $order = null)
    {
        $uv = UserVoucher::where('code', $code)
            ->where(function($q) use($user){
                $q->where('user_id',$user->id)
                  ->orWhere('gifted_to_phone',$user->phone);
            })
            ->where('is_active',true)
            ->where('remaining_amount','>',0)
            ->firstOrFail();

        // بدل min(... order->sub_total) فقط نستخدم ما مرّ إلينا من payUsingPackageAndWallet
        $uv->remaining_amount -= $discount;
        $uv->is_active = $uv->remaining_amount > 0;
        $uv->save();

        if ($order) {
            $order->update([
                'voucher_id'              => $uv->id,
                'voucher_discount_amount' => $discount,
            ]);
        }

        return $uv;
    }

}


// ALTER TABLE user_vouchers
// ADD COLUMN gifted_to_user_id BIGINT NULL AFTER user_id,
// ADD COLUMN gifted_to_phone VARCHAR(20) NULL AFTER gifted_to_user_id,
// ADD COLUMN gifted_at DATETIME NULL AFTER gifted_to_phone;