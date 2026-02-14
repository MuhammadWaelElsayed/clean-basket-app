<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\StatusSmsWhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'carts:notify-abandoned';

    /**
     * The console command description.
     */
    protected $description = 'Notify users about abandoned carts (draft orders created today)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check for orders created today only
        $today = Carbon::now()->startOfDay();
        $endOfToday = Carbon::now()->endOfDay();

        $abandonedOrders = Order::with('user')
            ->where('status', 'DRAFT')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<=', $endOfToday)
            ->whereDoesntHave('user', function ($query) {
                $query->whereNotNull('deleted_at');
            })
            ->get();

        $notificationService = new StatusSmsWhatsappService();
        $sentCount = 0;

        foreach ($abandonedOrders as $order) {
            $user = $order->user;

            if (!$user || !$user->phone) {
                Log::warning("Skipping order ID {$order->id}: User or phone not found");
                continue;
            }

             $userName = $user->first_name ?? 'عزيزنا العميل';

            try {
                if (isset($user->gender) && strtolower($user->gender) === 'female') {
                    $response = $notificationService->AbandonedCartFemale(
                        $userName,
                        $user->phone
                    );
                } else {
                    $response = $notificationService->AbandonedCartMale(
                        $userName,
                        $user->phone
                    );
                }

                Log::info("Abandoned cart notification sent to user ID {$user->id} for order ID {$order->id}", [
                    'response' => $response
                ]);

                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to send abandoned cart notification for order ID {$order->id}: " . $e->getMessage());
            }
        }

        $this->info("Abandoned cart notifications sent: {$sentCount} out of {$abandonedOrders->count()} orders");
        Log::info("Abandoned cart notifications completed: {$sentCount} sent");

        return Command::SUCCESS;
    }
}

