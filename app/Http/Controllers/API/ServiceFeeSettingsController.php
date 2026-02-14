<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ValidatePartner;
use App\Models\B2bClient;
use App\Models\PromoCode;
use App\Models\SettingsServiceFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceFeeSettingsController extends Controller
{
    /**
     * Get service fee settings
     *
     * @return JsonResponse
     */
    public function getSettings(): JsonResponse
    {
        try {
            $settings = SettingsServiceFee::getActiveSettings();

            if (!$settings) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service fee settings not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Service fee settings retrieved successfully',
                'data' => [
                    'is_enabled' => (bool)$settings->is_enabled,
                    'minimum_order_amount' => (float)$settings->minimum_order_amount,
                    'service_fee_amount' => (float)$settings->service_fee_amount,
                    'description' => $settings->description,
                    'currency' => env('CURRENCY', 'SAR'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve service fee settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate service fee for a given order amount
     *
     * @param float $subTotal
     * @param float $taxAmount
     * @return array
     */
    public function calculateServiceFee(float $subTotal, float $taxAmount = 0, $deliveryFees = 0, $serviceFees = 0): array
    {
        $settings = SettingsServiceFee::getActiveSettings();

        if($serviceFees === 0){
            if (!$settings || !$settings->is_enabled) {
                return [
                    'service_fee' => 0,
                    'service_fee_applied' => false,
                    'reason' => null
                ];
            }
        }
        // حساب المبلغ الإجمالي (المجموع الفرعي + الضريبة)
        $totalAmount = $subTotal + $taxAmount + $deliveryFees;

        if ($totalAmount < $settings->minimum_order_amount) {
            return [
                'service_fee' => $serviceFees > 0 ? $serviceFees : (float)$settings->service_fee_amount,
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

    /**
     * Get service fee calculation for preview
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function calculatePreview(Request $request): JsonResponse
    {
        $request->validate([
            'sub_total' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0'
        ]);

        try {
            $subTotal = (float)$request->sub_total;
            $taxAmount = (float)($request->tax_amount ?? 0);

            $serviceFeeData = $this->calculateServiceFee($subTotal, $taxAmount);

            return response()->json([
                'status' => true,
                'message' => 'Service fee calculation completed',
                'data' => array_merge($serviceFeeData, [
                    'sub_total' => $subTotal,
                    'tax_amount' => $taxAmount
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to calculate service fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function calculatePreviewForPartner(Request $request): JsonResponse
    {
        $request->validate([
            'order_amount' => 'required|numeric|min:0',
            'promo_code' => 'nullable|string',
        ]);

        $source = ValidatePartner::getSource($request->header('X-Source'));
        $serviceFee = $source['service_fee'] ?? 0;
        $deliveryFee = $source['delivery_fees'] ?? 0;

        $orderAmount = $request->order_amount;
        $taxPercentage = 15;
        $promoCode = $request->promo_code;
        $isPromoCodeValid = false;
        $taxableBase = (float)($orderAmount + $deliveryFee);
        $discountableAmount = $taxableBase;
        $discount = 0;

        if ($promoCode && $promo = PromoCode::where([
                'user_type' => 'All',
                'code' => $promoCode,
                'status' => 1,
            ])->first()) {

            if ($promo->promo_type === 'Percentage') {
                $percent = (float)($promo->discount_percentage ?? 0);
                $discount = round($discountableAmount * ($percent / 100), 2);
            } else {
                // Fixed amount
                $fixed = (float)($promo->discounted_amount ?? 0);
                $discount = min($fixed, $discountableAmount);
            }

            $isPromoCodeValid = true;
        }

        try {
            $subTotal = (float)$orderAmount;
            // Calculate tax amount (15% of subtotal)
            $taxAmount = $subTotal * ($taxPercentage / 100);

            $serviceFeeData = $this->calculateServiceFee($orderAmount - $discount, $taxAmount, $deliveryFee, $serviceFee);

            return response()->json([
                'status' => true,
                'message' => 'Service fee calculation completed',
                'data' => array_merge($serviceFeeData, [
                    'delivery_fee' => $deliveryFee,
                    'promo_code' => $promoCode,
                    'promo_code_valid' => $isPromoCodeValid,
                    'discount_amount' => $discount,
                    'tax_amount' => $taxAmount,
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to calculate service fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
