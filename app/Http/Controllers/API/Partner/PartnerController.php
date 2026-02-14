<?php

namespace App\Http\Controllers\API\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use App\Models\UserVoucher;
use App\Services\WorkingHoursService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function pickup(Request $req, WorkingHoursService $svc)
    {
        $lon = $req->header('X-Lon');
        $lat = $req->header('X-Lat');

        $vendor = $this->getAreaVendor($lat, $lon);

        if(!$vendor){
            return response()->json([
                'status'  => false,
                "message" => __('api')['order_vendor'],
                'data'    => [],
            ], 400);
        }

        $vendorId = $vendor->id;

        $validated = $req->validate([
            'date'         => ['nullable','date'], // YYYY-MM-DD
            'min_count'    => ['nullable','integer','min:1','max:50'],
            'days_horizon' => ['nullable','integer','min:1','max:14'],
            'slot_minutes' => ['nullable','integer','in:30,60,90,120'],
        ]);

        $slots = $svc->listPickupSlots(
            vendorId: (int)$vendorId,
            dateYmd: $validated['date'] ?? null,
            daysHorizon: $validated['days_horizon'] ?? 2,
            minCount: $validated['min_count'] ?? 6,
            slotMinutes: $validated['slot_minutes'] ?? 60
        );

        return response()->json([
            'status' => true,
            'data'   => $slots,
        ]);
    }

    public function delivery(Request $req, WorkingHoursService $svc)
    {
        $lon = $req->header('X-Lon');
        $lat = $req->header('X-Lat');

        $vendor = $this->getAreaVendor($lat, $lon);

        if(!$vendor){
            return response()->json([
                'status'  => false,
                "message" => __('api')['order_vendor'],
                'data'    => [],
            ], 400);
        }

        $vendorId = $vendor->id;

        $validated = $req->validate([
            'pickup_at'    => ['required','date'], // ISO أو "YYYY-MM-DD HH:mm"
            'gap_hours'    => ['nullable','integer','min:1','max:72'],
            'min_count'    => ['nullable','integer','min:1','max:50'],
            'days_horizon' => ['nullable','integer','min:1','max:14'],
            'slot_minutes' => ['nullable','integer','in:30,60,90,120'],
        ]);

        $pickupAt = Carbon::parse($validated['pickup_at']);

        $slots = $svc->listDeliverySlots(
            vendorId: (int)$vendorId,
            pickupAt: $pickupAt,
            gapHours: $validated['gap_hours'] ?? 48,          // شرطك: +20 ساعة
            daysHorizon: $validated['days_horizon'] ?? 5,
            minCount: $validated['min_count'] ?? 6,
            slotMinutes: $validated['slot_minutes'] ?? 60
        );

        return response()->json([
            'status' => true,
            'data'   => $slots,
        ]);
    }

    public function applyPromoCode(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'code'     => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        $deliveryFee = $order->delivery_fee;

        // --- Tax rate (default 15%) ---
        $taxRate = (float) env('TAX_RATE', 0.15);

        // --- Find active promo code definition ---
        $promo = PromoCode::where([
            'user_type' => 'All',
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
            ->where('user_id', $order->user_id)
            ->where('code_id', $promo->id)
            ->where('is_used', 0)
            ->first();

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
        if ($userCode && $promo->expiry === 'COUNT' && $promo->count !== null && $userCode->count !== null) {
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

    public function checkServiceAvailability(Request $request)
    {
        $lon = $request->header('X-Lon');
        $lat = $request->header('X-Lat');

        $vendor = $this->getAreaVendor($lat, $lon);

        if(!$vendor){
            return response()->json([
                'status'  => false,
                "message" => __('api')['order_vendor'],
                'data'    => [],
            ], 200);
        }

        return response()->json([
            'status'  => true,
            "message" => 'Service available for this location',
            'data'    => [],
        ], 200);
    }
}
