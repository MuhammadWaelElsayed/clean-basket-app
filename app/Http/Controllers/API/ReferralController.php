<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Referral;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function rewardReferral(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Find the referral for this user
        $referral = Referral::where('referred_id', $user->id)
            ->where('rewarded', false)
            ->first();

        if (!$referral) {
            return response()->json([
                'status' => false,
                'message' => 'No valid referral found for this user'
            ], 404);
        }

        $referrer = User::find($referral->referrer_id);

        if (!$referrer) {
            return response()->json([
                'status' => false,
                'message' => 'Referrer user not found'
            ], 404);
        }

        // Create new coupon
        $promoCode = PromoCode::create([
            'title' => 'Referral Reward',
            'code' => strtoupper(Str::random(10)), // Generate a random discount code
            'min_order' => 0, // Minimum requirement for the code to be valid
            'max_order' => 500, //Maximum request
            'expiry' => 'DATE',
            'from_date' => now(),
            'to_date' => now()->addDays(30),
            'promo_type' => 'Amount', // or 'Amount' as needed
            'discount_percentage' => 30, //Discount value 10%
            'user_type' => 'Selected',
        ]);

        // Associate the coupon with the referring user
        // Associate the coupon with the referrer and referred user
        UserPromoCode::create([
            "user_id" => $referrer->id,
            "code_id" => $promoCode->id
        ]);

        UserPromoCode::create([
            "user_id" => $user->id, // المستخدم المحال
            "code_id" => $promoCode->id
        ]);


        // Update the referral as rewarded
        $referral->update(['rewarded' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Referral reward granted successfully',
            'promo_code' => $promoCode->code
        ]);
    }

    /**
     * Get all users who used the referral code of the authenticated user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function usersUsedMyReferral(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
        // Get all referrals where this user is the referrer
        $referrals = Referral::where('referrer_id', $user->id)->get();
        $referredUserIds = $referrals->pluck('referred_id');
        $referredUsers = User::whereIn('id', $referredUserIds)->get(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at']);
        // Combine first_name and last_name into name
        $users = $referredUsers->map(function($user) {
            return [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
            ];
        });
        return response()->json([
            'status' => true,
            'message' => 'Users who used your referral code',
            'data' => $users,
        ]);
    }



    // public function rewardReferral(Request $request)
    // {
    //     $user = User::find($request->user_id);

    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not found'
    //         ], 404);
    //     }

    //     // Find the referral for this user
    //     $referral = Referral::where('referred_id', $user->id)
    //         ->where('rewarded', false)
    //         ->first();

    //     if (!$referral) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No valid referral found for this user'
    //         ], 404);
    //     }

    //     $referrer = User::find($referral->referrer_id);

    //     if (!$referrer) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Referrer user not found'
    //         ], 404);
    //     }

    //     // Add 30 SAR to the referrer's wallet
    //     $referrer->wallet()->firstOrCreate([])->transactions()->create([
    //         'type' => 'credit',
    //         'amount' => 30,
    //         'source' => 'referral_reward',
    //         'description' => 'Referral reward for inviting a user',
    //     ]);

    //     // Add 30 SAR to the referred user's wallet
    //     $user->wallet()->firstOrCreate([])->transactions()->create([
    //         'type' => 'credit',
    //         'amount' => 30,
    //         'source' => 'referral_reward',
    //         'description' => 'Referral reward for being referred',
    //     ]);

    //     // Update the referral as rewarded
    //     $referral->update(['rewarded' => true]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Referral reward added to wallets successfully'
    //     ]);
    // }
}
