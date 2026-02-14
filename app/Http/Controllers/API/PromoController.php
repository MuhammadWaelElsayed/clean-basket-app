<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPromoCode;
use App\Models\PromoCode;
use App\Models\Order;
use Carbon\Carbon;

class PromoController extends Controller
{


    public function myPromoCodes()
    {
        $codes = UserPromoCode::with('promoCode')->where('user_id', auth()->user()->id)->where(["is_used" => 0])->get();

        return  [
            "status" => true,
            "message" => "User Promo Codes",
            "data" => $codes,
        ];
    }

    // public function applyPromoCode(Request $request)
    // {
    //     $order = Order::findOrFail($request->order_id);
    //     $user = auth()->user();

    //     // حساب رسوم التوصيل من الباقة النشطة
    //     $activePackage = $user->userPackages()
    //         ->where('is_active', true)
    //         ->latest('start_date')
    //         ->with('package')
    //         ->first();

    //     $deliveryFee = $activePackage
    //         ? $activePackage->package->delivery_fee
    //         : 0;

    //     // حساب الإجمالي الكامل (sub_total + delivery_fee + tax)
    //     $taxRate = env('TAX_RATE', 0);
    //     $baseAmount = $order->sub_total + $deliveryFee;
    //     $taxAmount = round($baseAmount * $taxRate, 2);
    //     $grandTotal = round($baseAmount + $taxAmount, 2);

    //     // استخدام الإجمالي الكامل للتحقق من الشروط وحساب الخصم
    //     $order_amount = $grandTotal;

    //     $isExist = PromoCode::where(['code' => $request->code, 'status' => 1])->first();

    //     if ($isExist == null) {
    //         return [
    //             "status" => false,
    //             "message" => "Invalid Promo Code",
    //             "data" => [],
    //         ];
    //     }
    //     $code = UserPromoCode::with('promoCode')->where('user_id', auth()->user()->id)->where(['code_id' => $isExist->id, "is_used" => 0])
    //         ->first();
    //     if ($code == null) {
    //         return [
    //             "status" => false,
    //             "message" => "Invalid Promo Code",
    //             "data" => [],
    //         ];
    //     } elseif ($order_amount < $isExist->min_order) {
    //         return [
    //             "status" => false,
    //             "message" => "Sorry! your order amount is not enough to apply this code",
    //             "data" => [],
    //         ];
    //     } elseif ($isExist->expiry == 'DATE' && $isExist->from_date > date('Y-m-d') && $isExist->to_date < date('Y-m-d')) {
    //         return [
    //             "status" => false,
    //             "message" => "Date is Expired or Not Valid",
    //             "data" => [],
    //         ];
    //     }

    //     if ($isExist->expiry == 'COUNT' && $isExist->count == $code->count) {
    //         return [
    //             "status" => false,
    //             "message" => "Promo Code is already used",
    //             "data" => [],
    //         ];
    //     }
    //     // تحديد المبلغ المسموح بالخصم عليه (الحد الأدنى بين order_amount و max_order)
    //     $discountableAmount = $order_amount;
    //     if ($isExist->max_order != null) {
    //         $discountableAmount = min($order_amount, $isExist->max_order);
    //     }

    //     // حساب الخصم على المبلغ المسموح بالخصم فقط
    //     $discount = 0;
    //     if ($code->promoCode->promo_type == 'Percentage') {
    //         $discount = round(($discountableAmount / 100) * $code->promoCode->discount_percentage, 2);
    //     } else {
    //         $discount = isset($code->promoCode->discounted_amount) ? (float) $code->promoCode->discounted_amount : 0;
    //     }

    //     // استخدام الإجمالي الكامل المحسوب أعلاه
    //     $orderGrandTotal = $grandTotal;
    //     logger('PromoType: ' . $code->promoCode->promo_type . ', Discountable Amount: ' . $discountableAmount . ', Original Discount: ' . $discount . ', Grand Total: ' . $orderGrandTotal);
    //     $discount = min($discount, $discountableAmount); // لا يتجاوز الخصم المبلغ المسموح بالخصم
    //     $finalTotal = round($orderGrandTotal - $discount, 2);
    //     $finalTotal = max($finalTotal, 0); // لا يسمح بالسالب

    //     $code->promoCode->discounted_amount = strval($discount);
    //     $code->order_amount = $finalTotal;

    //     logger('Discount Amount:' . $discount . ', Grand Before Promo:' . $orderGrandTotal . ', Grand After Promo:' . $finalTotal);
    //     return [
    //         "status" => true,
    //         "message" => "Promo Code applied successfully!",
    //         "data" => $code,
    //     ];
    // }

    public function applyPromoCode(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'code'     => 'required|string',
        ]);

        $user  = auth()->user();
        $order = Order::findOrFail($request->order_id);

        // Ensure the order belongs to the current user
        if ((int) $order->user_id !== (int) $user->id) {
            return [
                "status"  => false,
                "message" => "Unauthorized: order does not belong to the current user.",
                "data"    => [],
            ];
        }

        // Ensure order is eligible (not paid/cancelled)
        if (in_array($order->pay_status, ['Paid'], true) || in_array($order->status, ['CANCELLED'], true)) {
            return [
                "status"  => false,
                "message" => "Order not eligible for promo.",
                "data"    => [],
            ];
        }

        // --- Delivery fee from active package (if any) ---
        // If you have a different delivery fee source, adjust here.
        $activePackage = $user->userPackages()
            ->where('is_active', true)
            ->latest('start_date')
            ->with('package')
            ->first();

        $deliveryFee = 0.0;
        if ($activePackage && $activePackage->package) {
            // If your business rule: delivery fee comes from active package
            $deliveryFee = (float) ($activePackage->package->delivery_fee ?? 0);
        } else {
            // Or if you already store delivery_fee on the order, you can use:
            // $deliveryFee = (float) ($order->delivery_fee ?? 0);
            $deliveryFee = (float) ($order->delivery_fee ?? 0);
        }

        // --- Tax rate (default 15%) ---
        $taxRate = (float) env('TAX_RATE', 0.15);

        // --- Find active promo code definition ---
        $promo = PromoCode::where([
            'code'   => $request->code,
            'status' => 1,
        ])->first();

        if (!$promo) {
            return [
                "status"  => false,
                "message" => "Invalid Promo Code",
                "data"    => [],
            ];
        }

        // --- Ensure the user owns an unused instance of that code ---
        $userCode = UserPromoCode::with('promoCode')
            ->where('user_id', $user->id)
            ->where('code_id', $promo->id)
            ->where('is_used', 0)
            ->first();

        if (!$userCode) {
            return [
                "status"  => false,
                "message" => "Invalid Promo Code",
                "data"    => [],
            ];
        }

        // --- Date expiry validation (if expiry type is DATE) ---
        if ($promo->expiry === 'DATE') {
            $today = Carbon::today();
            $from  = $promo->from_date ? Carbon::parse($promo->from_date) : null;
            $to    = $promo->to_date   ? Carbon::parse($promo->to_date)   : null;

            // Not valid if today is before "from" OR after "to"
            if (($from && $today->lt($from)) || ($to && $today->gt($to))) {
                return [
                    "status"  => false,
                    "message" => "Date is Expired or Not Valid",
                    "data"    => [],
                ];
            }
        }

        // --- Count expiry validation (if expiry type is COUNT) ---
        // If your logic stores per-user usage in $userCode->count and promo->count is the limit:
        if ($promo->expiry === 'COUNT' && $promo->count !== null && $userCode->count !== null) {
            if ((int) $promo->count <= (int) $userCode->count) {
                return [
                    "status"  => false,
                    "message" => "Promo Code is already used",
                    "data"    => [],
                ];
            }
        }

        // --- Minimum order check uses the taxable base BEFORE VAT ---
        // Business rule: Use taxable base (sub_total + deliveryFee) rather than grand total.
        $subTotal    = (float) ($order->sub_total ?? 0);
        $serviceFee  = (float) ($order->service_fee_applied ? ($order->service_fee ?? 0) : 0);
        $taxableBase = (float) ($subTotal + $deliveryFee);

        if ($promo->min_order !== null && $taxableBase < (float) $promo->min_order) {
            return [
                "status"  => false,
                "message" => "Sorry! your order amount is not enough to apply this code",
                "data"    => [],
            ];
        }

        // --- Max order cap for discountable amount ---
        $discountableAmount = $taxableBase;
        if ($promo->max_order !== null) {
            $discountableAmount = min($taxableBase, (float) $promo->max_order);
        }

        // --- Calculate discount ---
        $discount = 0.0;
        if ($promo->promo_type === 'Percentage') {
            $percent  = (float) ($promo->discount_percentage ?? 0);
            $discount = round($discountableAmount * ($percent / 100), 2);
        } else {
            // Fixed amount
            $fixed    = (float) ($promo->discounted_amount ?? 0);
            $discount = min($fixed, $discountableAmount);
        }

        // Safety: discount cannot exceed discountable amount
        $discount = min($discount, $discountableAmount);

        // --- Recompute VAT after discount ---
        // VAT base is taxableBase - discount
        $netTaxableBase = max($taxableBase - $discount, 0);
        $vatBefore      = round($taxableBase * $taxRate, 2);
        $vatAfter       = round($netTaxableBase * $taxRate, 2);
        $discountVat    = round($vatBefore - $vatAfter, 2); // Just for debugging/inspection

        // --- Final grand total ---
        // Grand = (net taxable base) + VAT after discount + service fee (no discount)
        $finalTotal = round($netTaxableBase + $vatAfter + $serviceFee, 2);

        // --- For comparison: original grand total BEFORE promo ---
        $originalVat   = $vatBefore;
        $originalGrand = round($taxableBase + $originalVat + $serviceFee, 2);

        // --- Logging (optional) ---
        logger("Promo.apply", [
            'order_id'            => $order->id,
            'code'                => $promo->code,
            'promo_type'          => $promo->promo_type,
            'percent'             => $promo->discount_percentage,
            'fixed'               => $promo->discounted_amount,
            'sub_total'           => $subTotal,
            'delivery_fee'        => $deliveryFee,
            'service_fee'         => $serviceFee,
            'tax_rate'            => $taxRate,
            'taxable_base'        => $taxableBase,
            'discountable_amount' => $discountableAmount,
            'applied_discount'    => $discount,
            'vat_before'          => $vatBefore,
            'vat_after'           => $vatAfter,
            'discount_vat'        => $discountVat,
            'original_grand'      => $originalGrand,
            'final_total'         => $finalTotal,
        ]);

        // --- Prepare response payload (no DB persistence here) ---
        // Mutate in-memory objects to reflect applied numbers for API response only
        $userCode->promoCode->discounted_amount = number_format($discount, 2, '.', '');
        $userCode->order_amount                 = $finalTotal;

        // Optional: provide a clean calc block for the client
        $calc = [
            'sub_total'               => $subTotal,
            'delivery_fee'            => $deliveryFee,
            'service_fee'             => $serviceFee,
            'tax_rate'                => $taxRate,
            'taxable_base_before'     => $taxableBase,
            'discountable_amount'     => $discountableAmount,
            'applied_discount'        => $discount,
            'vat_before'              => $originalVat,
            'vat_after'               => $vatAfter,
            'discount_vat'            => $discountVat,
            'grand_total_before_promo'=> $originalGrand,
            'grand_total_after_promo' => $finalTotal,
        ];

        return [
            "status"  => true,
            "message" => "Promo Code applied successfully!",
            "data"    => [
                'user_promo' => $userCode, // includes promoCode with mutated discounted_amount + order_amount
                'calc'       => $calc,
            ],
        ];
    }

}
