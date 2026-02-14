<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DriverRequestService;
use App\Models\DriverRequest;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpireDriverRequests extends Command
{
    protected $signature = 'drivers:expire-requests
                          {--dry-run : Run without making changes}
                          {--phase= : Only process specific phase (pickup or delivery)}
                          {--verbose : Show detailed output}';

    protected $description = 'Expire old driver requests and reassign orders for both pickup and delivery phases';

    protected $driverRequestService;
    protected $statistics = [
        'expired_requests' => 0,
        'pickup_phase' => [
            'orders_found' => 0,
            'requests_sent' => 0,
            'no_drivers_available' => 0,
        ],
        'delivery_phase' => [
            'orders_found' => 0,
            'requests_sent' => 0,
            'no_drivers_available' => 0,
        ],
        'admin_notifications_sent' => 0,
    ];

    public function __construct(DriverRequestService $driverRequestService)
    {
        parent::__construct();
        $this->driverRequestService = $driverRequestService;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $isDryRun = $this->option('dry-run');
        $phaseFilter = $this->option('phase');

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  DRIVER REQUEST EXPIRATION & REASSIGNMENT SYSTEM');
        $this->info('  Started at: ' . Carbon::now()->format('Y-m-d H:i:s'));
        if ($isDryRun) {
            $this->warn('  MODE: DRY RUN (No changes will be made)');
        }
        if ($phaseFilter) {
            $this->info('  PHASE FILTER: ' . strtoupper($phaseFilter));
        }
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Step 1: Expire old requests
        $this->expireOldRequests($isDryRun);

        // Step 2: Handle pickup phase
        if (!$phaseFilter || $phaseFilter === 'pickup') {
            $this->handlePickupPhase($isDryRun);
        }

        // Step 3: Handle delivery phase
        if (!$phaseFilter || $phaseFilter === 'delivery') {
            $this->handleDeliveryPhase($isDryRun);
        }

        // Step 4: Display summary
        $this->displaySummary($startTime);

        // Step 5: Log to file
        $this->logToFile();

        return 0;
    }

    /**
     * Expire old pending requests
     */
    protected function expireOldRequests($isDryRun)
    {
        $this->info('🕐 STEP 1: Expiring Old Requests');
        $this->info('─────────────────────────────────');

        $expiredRequests = DriverRequest::expired()->get();
        $this->statistics['expired_requests'] = $expiredRequests->count();

        if ($expiredRequests->isEmpty()) {
            $this->info('✓ No expired requests found');
        } else {
            $this->warn("⚠ Found {$this->statistics['expired_requests']} expired requests");

            if ($this->option('verbose')) {
                $table = [];
                foreach ($expiredRequests as $request) {
                    $table[] = [
                        $request->id,
                        $request->order->order_code ?? 'N/A',
                        $request->request_type,
                        $request->driver->name ?? 'N/A',
                        $request->expires_at->diffForHumans(),
                    ];
                }
                $this->table(
                    ['ID', 'Order', 'Type', 'Driver', 'Expired'],
                    $table
                );
            }

            if (!$isDryRun) {
                $this->driverRequestService->expireOldRequests();
                $this->info('✓ Marked all expired requests as EXPIRED');
            } else {
                $this->comment('• [DRY RUN] Would mark as EXPIRED');
            }
        }

        $this->newLine();
    }

    /**
     * Handle orders in pickup phase without driver
     */
    protected function handlePickupPhase($isDryRun)
    {
        $this->info('📦 STEP 2: PICKUP Phase - Finding Orders Without Pickup Driver');
        $this->info('───────────────────────────────────────────────────────────────');

        $ordersNeedingPickupDriver = Order::whereNull('pickup_driver_id')
            ->where('status', 'PLACED')
            ->whereHas('pickupRequests', function($query) {
                $query->whereIn('status', ['EXPIRED', 'REJECTED']);
            })
            ->with(['deliveryAddress', 'vendor', 'user'])
            ->get();

        $this->statistics['pickup_phase']['orders_found'] = $ordersNeedingPickupDriver->count();

        if ($ordersNeedingPickupDriver->isEmpty()) {
            $this->info('✓ No orders needing pickup driver');
            $this->newLine();
            return;
        }

        $this->warn("⚠ Found {$ordersNeedingPickupDriver->count()} orders needing pickup driver");
        $this->newLine();

        foreach ($ordersNeedingPickupDriver as $index => $order) {
            $orderNum = $index + 1;
            $this->info("[{$orderNum}/{$ordersNeedingPickupDriver->count()}] Processing Order #{$order->order_code}");

            // Check if order has pending PICKUP requests
            $hasPendingPickup = $this->driverRequestService->hasPendingRequests($order, 'PICKUP');

            if ($hasPendingPickup) {
                $this->comment('  • Already has pending PICKUP requests, skipping...');
                continue;
            }

            $address = $order->deliveryAddress;

            if (!$address) {
                $this->error('  ✗ No delivery address found, skipping...');
                continue;
            }

            // Get request statistics
            $stats = $this->driverRequestService->getOrderRequestStats($order, 'PICKUP');
            $this->comment("  • Previous attempts: {$stats['total_sent']} sent, {$stats['rejected']} rejected, {$stats['expired']} expired");

            if (!$isDryRun) {
                // Resend PICKUP requests
                $result = $this->driverRequestService->sendPickupRequestToDrivers(
                    $order,
                    $order->vendor_id,
                    $address->lat,
                    $address->lng
                );

                if ($result['success']) {
                    $this->info("  ✓ Sent PICKUP requests to {$result['drivers_found']} nearby drivers");
                    $this->statistics['pickup_phase']['requests_sent'] += $result['drivers_found'];
                } else {
                    $this->error("  ✗ No drivers available for PICKUP");
                    $this->statistics['pickup_phase']['no_drivers_available']++;

                    // Notify admin
                    $this->notifyAdminAboutDriverShortage($order, 'PICKUP', $isDryRun);
                }
            } else {
                $this->comment('  • [DRY RUN] Would send PICKUP requests to nearby drivers');
            }

            $this->newLine();
        }
    }

    /**
     * Handle orders in delivery phase without driver
     */
    protected function handleDeliveryPhase($isDryRun)
    {
        $this->info('🚚 STEP 3: DELIVERY Phase - Finding Orders Without Delivery Driver');
        $this->info('────────────────────────────────────────────────────────────────');

        $ordersNeedingDeliveryDriver = Order::whereNull('delivery_driver_id')
            ->where('status', 'READY_TO_DELIVER')
            ->whereHas('deliveryRequests', function($query) {
                $query->whereIn('status', ['EXPIRED', 'REJECTED']);
            })
            ->with(['deliveryAddress', 'vendor', 'user', 'pickupDriver'])
            ->get();

        $this->statistics['delivery_phase']['orders_found'] = $ordersNeedingDeliveryDriver->count();

        if ($ordersNeedingDeliveryDriver->isEmpty()) {
            $this->info('✓ No orders needing delivery driver');
            $this->newLine();
            return;
        }

        $this->warn("⚠ Found {$ordersNeedingDeliveryDriver->count()} orders needing delivery driver");
        $this->newLine();

        foreach ($ordersNeedingDeliveryDriver as $index => $order) {
            $orderNum = $index + 1;
            $this->info("[{$orderNum}/{$ordersNeedingDeliveryDriver->count()}] Processing Order #{$order->order_code}");

            if ($order->pickupDriver) {
                $this->comment("  • Pickup handled by: {$order->pickupDriver->name}");
            }

            // Check if order has pending DELIVERY requests
            $hasPendingDelivery = $this->driverRequestService->hasPendingRequests($order, 'DELIVERY');

            if ($hasPendingDelivery) {
                $this->comment('  • Already has pending DELIVERY requests, skipping...');
                continue;
            }

            $address = $order->deliveryAddress;

            if (!$address) {
                $this->error('  ✗ No delivery address found, skipping...');
                continue;
            }

            // Get request statistics
            $stats = $this->driverRequestService->getOrderRequestStats($order, 'DELIVERY');
            $this->comment("  • Previous attempts: {$stats['total_sent']} sent, {$stats['rejected']} rejected, {$stats['expired']} expired");

            if (!$isDryRun) {
                // Resend DELIVERY requests
                $result = $this->driverRequestService->sendDeliveryRequestToDrivers(
                    $order,
                    $order->vendor_id,
                    $address->lat,
                    $address->lng
                );

                if ($result['success']) {
                    $this->info("  ✓ Sent DELIVERY requests to {$result['drivers_found']} nearby drivers");
                    $this->statistics['delivery_phase']['requests_sent'] += $result['drivers_found'];
                } else {
                    $this->error("  ✗ No drivers available for DELIVERY");
                    $this->statistics['delivery_phase']['no_drivers_available']++;

                    // Notify admin
                    $this->notifyAdminAboutDriverShortage($order, 'DELIVERY', $isDryRun);
                }
            } else {
                $this->comment('  • [DRY RUN] Would send DELIVERY requests to nearby drivers');
            }

            $this->newLine();
        }
    }

    /**
     * Notify admin about driver shortage
     */
    protected function notifyAdminAboutDriverShortage($order, $phase, $isDryRun = false)
    {
        $phaseText = $phase ? " ({$phase} phase)" : '';

        $data = [
            "title" => "Driver Shortage Alert{$phaseText}",
            "message" => "Order #{$order->order_code} cannot find available drivers{$phaseText}. Immediate attention required.",
            "link" => "admin/order-details/{$order->id}",
        ];

        if (!$isDryRun) {
            try {
                app(Controller::class)->sendNotifications($data, 'admin');
                $this->statistics['admin_notifications_sent']++;
                $this->warn("  ⚠ Admin notified about driver shortage");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to notify admin: {$e->getMessage()}");
                Log::error("Failed to send admin notification: {$e->getMessage()}");
            }
        } else {
            $this->comment("  • [DRY RUN] Would notify admin about shortage");
        }
    }

    /**
     * Display summary statistics
     */
    protected function displaySummary($startTime)
    {
        $executionTime = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  EXECUTION SUMMARY');
        $this->info('═══════════════════════════════════════════════════════════');

        $summaryTable = [
            ['Requests Expired', $this->statistics['expired_requests']],
            ['─────────────────', '──────'],
            ['PICKUP Phase:', ''],
            ['  Orders Found', $this->statistics['pickup_phase']['orders_found']],
            ['  Requests Sent', $this->statistics['pickup_phase']['requests_sent']],
            ['  No Drivers Available', $this->statistics['pickup_phase']['no_drivers_available']],
            ['─────────────────', '──────'],
            ['DELIVERY Phase:', ''],
            ['  Orders Found', $this->statistics['delivery_phase']['orders_found']],
            ['  Requests Sent', $this->statistics['delivery_phase']['requests_sent']],
            ['  No Drivers Available', $this->statistics['delivery_phase']['no_drivers_available']],
            ['─────────────────', '──────'],
            ['Admin Notifications', $this->statistics['admin_notifications_sent']],
            ['Execution Time', $executionTime . ' seconds'],
        ];

        $this->table(['Metric', 'Value'], $summaryTable);

        // Calculate totals
        $totalRequestsSent = $this->statistics['pickup_phase']['requests_sent'] +
            $this->statistics['delivery_phase']['requests_sent'];
        $totalNoDrivers = $this->statistics['pickup_phase']['no_drivers_available'] +
            $this->statistics['delivery_phase']['no_drivers_available'];

        if ($totalRequestsSent > 0) {
            $this->info("✓ Successfully sent {$totalRequestsSent} driver requests");
        }

        if ($totalNoDrivers > 0) {
            $this->warn("⚠ {$totalNoDrivers} orders have no available drivers!");
        }

        if ($this->statistics['expired_requests'] === 0 &&
            $this->statistics['pickup_phase']['orders_found'] === 0 &&
            $this->statistics['delivery_phase']['orders_found'] === 0) {
            $this->info('✓ All systems running smoothly - no action needed');
        }

        $this->info('═══════════════════════════════════════════════════════════');
    }

    /**
     * Log execution to file
     */
    protected function logToFile()
    {
        Log::channel('daily')->info('Driver Request Expiration Command Executed', [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'statistics' => $this->statistics,
        ]);
    }
}
