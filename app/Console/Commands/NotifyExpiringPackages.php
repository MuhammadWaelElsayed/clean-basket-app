<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackage;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyExpiringPackages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'packages:notify-expiring';

    /**
     * The console command description.
     */
    protected $description = 'Notify users when their packages are about to expire or have exhausted their credit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->startOfDay();

        // 1️⃣ إشعارات قبل 3 أيام من انتهاء الصلاحية
        $targetDate = $now->copy()->addDays(3);
        $expiring = UserPackage::with('user', 'package')
            ->where('is_active', true)
            ->whereDate('end_date', '=', $targetDate)
            ->get();

        foreach ($expiring as $up) {
            $user    = $up->user;
            $package = $up->package;

            Controller::sendNotifications([
                'title'      => "Your package \"{$package->name}\" expires soon",
                'title_ar'   => "ستنتهي صلاحية باقتك \"{$package->name}\" قريبًا",
                'message'    => "Your subscription to {$package->name} will expire in 3 days.",
                'message_ar' => "ستنتهي صلاحية اشتراكك في باقة {$package->name} خلال 3 أيام.",
                'user'       => $user,
            ], 'user');

            Log::info("Expiry reminder sent to user ID {$user->id} for package ID {$up->id} expiring on {$up->end_date}");
        }

        // 2️⃣ إشعارات عند استنفاد الرصيد
        $exhausted = UserPackage::with('user', 'package')
            ->where('is_active', true)
            ->where('remaining_credit', '<=', 0)
            ->get();

        foreach ($exhausted as $up) {
            $user    = $up->user;
            $package = $up->package;

            Controller::sendNotifications([
                'title'      => "Your package \"{$package->name}\" has no credit left",
                'title_ar'   => "رصيد باقتك \"{$package->name}\" قد نفد",
                'message'    => "Please recharge or upgrade to continue using our service.",
                'message_ar' => "يرجى إعادة الشحن أو الترقية للاستمرار في الخدمة.",
                'user'       => $user,
            ], 'user');

            Log::info("Exhaustion reminder sent to user ID {$user->id} for package ID {$up->id}");
        }

        return Command::SUCCESS;
    }
}


// * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
// php artisan packages:notify-expiring
