<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use Illuminate\Console\Command;
use App\Models\UserPackage;
use Illuminate\Support\Facades\Log;

class AutoRenewPackages extends Command
{
    protected $signature   = 'packages:auto-renew';
    protected $description = 'Automatically renew expired packages if auto_renew is enabled';

    public function handle()
    {
        $now = now();

        $expired = UserPackage::with('user', 'package', 'user.wallet')
            ->where('is_active', true)
            ->where('auto_renew', true)
            ->where(function($q) use ($now) {
                $q->where(function($qq) use ($now) {
                    $qq->whereNotNull('end_date')
                       ->where('end_date', '<=', $now);
                })
                ->orWhere('remaining_credit', '<=', 0);
            })
            ->get();

        foreach ($expired as $old) {
            $user    = $old->user;
            $package = $old->package;
            $price   = $package->price;
            $cashback= $package->cashback_amount;
            $total   = $price + $cashback;

            // إلغاء الباقة القديمة وتصفير رصيدها
            $old->update([
                'is_active'        => false,
                'remaining_credit' => 0,
            ]);

            // تحقق من رصيد المحفظة
            if (! $user->wallet || $user->wallet->balance < $price) {
                Log::warning("Auto-renew skipped for user {$user->id}: insufficient wallet balance.");
                continue;
            }

            // خصم من المحفظة وتسجيل العملية
            $user->wallet->decrement('balance', $price);
            $user->wallet->transactions()->create([
                'type'        => 'debit',
                'amount'      => $price,
                'source'      => 'system',
                'description' => "Auto-renew package {$package->name}",
            ]);

            $paymentResponse = [
                'source' => 'wallet',
                'amount' => $price,
                'note'   => 'Auto-renew charged from wallet',
            ];

            // إنشاء اشتراك جديد: فقط Basic له end_date زمنية
            $start = $now;
            $end   = ($package->name_en === 'Basic')
                ? $now->copy()->addDays($package->duration_days)
                : null;

            $new = $user->userPackages()->create([
                'package_id'       => $package->id,
                'total_credit'     => $total,
                'remaining_credit' => $total,
                'start_date'       => $start,
                'end_date'         => $end,
                'is_active'        => true,
                'auto_renew'       => true,
                'payment_response' => $paymentResponse,
            ]);

            $new->transactions()->create([
                'type'        => 'credit',
                'amount'      => $total,
                'description' => 'Auto-renew package',
            ]);

            // إشعار المستخدم: وضّح صلاحية الاشتراك حسب الباقة
            $message = $end
                ? "Your subscription to {$package->name} has been renewed until {$end->toDateString()}."
                : "Your subscription to {$package->name} has been renewed and will remain active until your balance is fully consumed.";

            $message_ar = $end
                ? "تم تجديد اشتراكك في باقة {$package->name} حتى تاريخ {$end->toDateString()}."
                : "تم تجديد اشتراكك في باقة {$package->name} وسيظل فعالاً حتى استهلاك كامل الرصيد.";

            Controller::sendNotifications([
                'title'      => "Your package has been auto-renewed",
                'title_ar'   => "تم تجديد باقتك تلقائيًا",
                'message'    => $message,
                'message_ar' => $message_ar,
                'user'       => $user,
            ], 'user');

            Log::info("Package auto-renewed for user {$user->id}, new package ID {$new->id}");
        }

        return Command::SUCCESS;
    }
}

// * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
