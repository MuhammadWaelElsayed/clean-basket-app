<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseHoldTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-hold-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredOrders = Order::where('pay_status', '!=', 'Paid')
            ->whereNotNull('hold_transaction_id')
            ->where('created_at', '<=', Carbon::now()->subMinutes(30))
            ->get();

        $released = 0;

        foreach ($expiredOrders as $order) {
            $transaction = WalletTransaction::find($order->hold_transaction_id);

            if ($transaction && $transaction->status === 'on_hold') {
                $transaction->status = 'cancelled';
                $transaction->save();
                $released++;

                // إرسال إشعار للمستخدم
                $user = $order->user;

                try {
                    $title      = "Order Hold Cancelled";
                    $title_ar   = "تم إلغاء حجز الطلب";
                    $message    = "Your order #{$order->order_code} was not confirmed in time and the hold has been released.";
                    $message_ar = "لم يتم تأكيد طلبك رقم {$order->order_code} في الوقت المحدد وتم إلغاء الحجز.";

                    // استدعاء sendNotifications() لو كانت global في controller
                    app()->call('App\Http\Controllers\Controller@sendNotifications', [[
                        "title"      => $title,
                        "title_ar"   => $title_ar,
                        "message"    => $message,
                        "message_ar" => $message_ar,
                        "user"       => $user,
                        "order"      => $order,
                    ], 'user']);
                } catch (\Exception $e) {
                    Log::warning("Failed to notify user for order {$order->id}: " . $e->getMessage());
                }


                $this->info("Released hold for order #{$order->id}, transaction #{$transaction->id}");
            }
        }

        $this->info("✅ {$released} hold transaction(s) released.");
    }
}


// * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
