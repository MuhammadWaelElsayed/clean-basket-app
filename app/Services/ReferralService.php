<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Process referral rewards when an order is delivered
     *
     * @param Order $order
     * @return void
     */
    public function processReferralReward(Order $order)
    {
        try {
            $userId = $order->user_id;
            $user = User::find($userId);

            if (!$user) {
                Log::warning("ReferralService: User not found for order {$order->id}");
                return;
            }

            // التحقق من أن هذا أول طلب للمستخدم تم تسليمه
            $isFirstDeliveredOrder = Order::where('user_id', $userId)
                ->where('status', 'DELIVERED')
                ->where('id', '!=', $order->id) // استثناء الطلب الحالي
                ->count() == 0;

            if (!$isFirstDeliveredOrder) {
                Log::info("ReferralService: Order {$order->id} is not first delivered order for user {$userId}");
                return;
            }

            // البحث عن referral record للمستخدم
            $referral = Referral::where('referred_id', $userId)
                ->where('rewarded', false)
                ->first();

            if (!$referral) {
                // إذا لم يكن هناك referral، حاول إنشاء واحد من user->referral_used
                if ($user->referral_used) {
                    $referrer = User::where('referral_code', $user->referral_used)->first();

                    if ($referrer) {
                        $referral = Referral::create([
                            'referrer_id' => $referrer->id,
                            'referred_id' => $userId,
                            'referral_code' => $user->referral_used,
                        ]);
                    }
                }
            }

            if (!$referral || $referral->rewarded) {
                Log::info("ReferralService: No valid referral found for user {$userId}");
                return;
            }

            $referrer = User::find($referral->referrer_id);
            $referred = User::find($referral->referred_id);

            if (!$referrer || !$referred) {
                Log::warning("ReferralService: Referrer or referred user not found for referral {$referral->id}");
                return;
            }

            // إضافة 30 SAR للشخص الذي قام بالإحالة
            $referrerWallet = $referrer->wallet()->firstOrCreate([]);
            $referrerWallet->transactions()->create([
                'type' => 'credit',
                'amount' => 30,
                'source' => 'referral_reward',
                'description' => 'Referral reward for inviting a user',
            ]);
            $referrerWallet->increment('balance', 30);

            // إضافة 30 SAR للشخص المحال
            $referredWallet = $referred->wallet()->firstOrCreate([]);
            $referredWallet->transactions()->create([
                'type' => 'credit',
                'amount' => 30,
                'source' => 'referral_reward',
                'description' => 'Referral reward for being referred',
            ]);
            $referredWallet->increment('balance', 30);

            // تحديث حالة المكافأة
            $referral->update(['rewarded' => true]);

            // إرسال إشعارات
            $this->sendReferralNotifications($referrer, $referred);

            Log::info("ReferralService: Successfully processed referral reward for order {$order->id}");

        } catch (\Exception $e) {
            Log::error("ReferralService: Error processing referral reward for order {$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Send notifications to referrer and referred users
     *
     * @param User $referrer
     * @param User $referred
     * @return void
     */
    private function sendReferralNotifications(User $referrer, User $referred)
    {
        try {
            // إشعار للمستخدم المحال
            $this->sendNotification([
                'title' => 'You have been referred to ' . $referrer->name,
                'message' => 'You have been rewarded with 30 SAR for being referred',
                'user' => $referred,
            ]);

            // إشعار للشخص الذي قام بالإحالة
            $this->sendNotification([
                'title' => 'Referral reward earned!',
                'message' => 'You have been rewarded with 30 SAR for referring ' . $referred->name,
                'user' => $referrer,
            ]);

        } catch (\Exception $e) {
            Log::error("ReferralService: Error sending notifications: " . $e->getMessage());
        }
    }

    /**
     * Send notification to user
     *
     * @param array $data
     * @return void
     */
    private function sendNotification(array $data)
    {
        try {
            // Use the existing sendNotifications method from Controller
            Controller::sendNotifications($data, 'user');
        } catch (\Exception $e) {
            Log::error("ReferralService: Error sending notification: " . $e->getMessage());
        }
    }
}
