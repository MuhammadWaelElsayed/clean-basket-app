<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\DriverRequest;
use App\Services\DriverRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FindDriverForOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;
    public $requestType;
    public $currentRadius;
    public $lat;
    public $lng;
    public $vendorId;
    public $attemptNumber;

    const INITIAL_RADIUS = 2;
    const MAX_RADIUS = 30;
    const RADIUS_INCREMENT = 2;
    const WAIT_TIME_SECONDS = 60;

    public $timeout = 300;
    public $tries = 1;

    const MAX_ATTEMPTS = 30; // Hard limit on attempts

    public function __construct(Order $order, $requestType, $lat, $lng, $vendorId, $currentRadius = self::INITIAL_RADIUS, $attemptNumber = 1)
    {
        $this->order = $order;
        $this->requestType = $requestType;
        $this->currentRadius = $currentRadius;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->vendorId = $vendorId;
        $this->attemptNumber = $attemptNumber;
    }

    public function handle(DriverRequestService $driverRequestService)
    {
        if ($this->attemptNumber > self::MAX_ATTEMPTS) {
            Log::warning("Exceeded maximum attempts ({self::MAX_ATTEMPTS}) for order #{$this->order->order_code}");
            $this->notifyNoDriversAvailable();
            Log::warning('Engine: sending order to provider');
            self::dispatch(new SendOrderToProvider($this->order));
            return;
        }
        // Cache key for tracking job progress
        $cacheKey = "driver_search:{$this->order->id}:{$this->requestType}";
        $upcomingKey = "upcoming_job:{$this->order->id}:{$this->requestType}";

        // Check if this was a scheduled job and update its status
        $upcomingJob = Cache::get($upcomingKey);
        if ($upcomingJob && $upcomingJob['status'] === 'scheduled') {
            Cache::forget($upcomingKey);
            Log::info("Scheduled job for {$this->requestType} on order #{$this->order->order_code} has started");
        }

        // Update cache with current job status
        Cache::put($cacheKey, [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'request_type' => $this->requestType,
            'current_radius' => $this->currentRadius,
            'attempt_number' => $this->attemptNumber,
            'status' => 'searching',
            'last_activity' => now(),
            'started_at' => Cache::get($cacheKey)['started_at'] ?? now(),
        ], now()->addMinutes(30));

        // Refresh order to get latest state
        $this->order = Order::find($this->order->id);

        if (!$this->order) {
            Log::warning("Order no longer exists. Stopping job.");
            Cache::forget($cacheKey);
            return;
        }

        // Check if driver already assigned
        if ($this->isDriverAlreadyAssigned()) {
            Log::info("Driver already assigned for {$this->requestType} on order #{$this->order->order_code}. Stopping job.");

            Cache::put($cacheKey, [
                'order_id' => $this->order->id,
                'order_code' => $this->order->order_code,
                'request_type' => $this->requestType,
                'current_radius' => $this->currentRadius,
                'attempt_number' => $this->attemptNumber,
                'status' => 'completed',
                'last_activity' => now(),
                'completed_at' => now(),
            ], now()->addHours(1));

            return;
        }

        // Check if any driver has accepted
        if ($this->hasAcceptedDriver()) {
            Log::info("Driver accepted {$this->requestType} for order #{$this->order->order_code}. Stopping job.");

            Cache::put($cacheKey, [
                'order_id' => $this->order->id,
                'order_code' => $this->order->order_code,
                'request_type' => $this->requestType,
                'current_radius' => $this->currentRadius,
                'attempt_number' => $this->attemptNumber,
                'status' => 'completed',
                'last_activity' => now(),
                'completed_at' => now(),
            ], now()->addHours(1));

            return;
        }

        Log::info("Attempt #{$this->attemptNumber}: Finding drivers at {$this->currentRadius}km radius for {$this->requestType} on order #{$this->order->order_code}");

        // Try assigned drivers first
        $drivers = $driverRequestService->getNearbyAvailableDriversPublic(
            $this->vendorId,
            $this->lat,
            $this->lng,
            true,
            $this->currentRadius
        );

        // If no assigned drivers, try unassigned
        if ($drivers->isEmpty()) {
            Log::info("No assigned drivers at {$this->currentRadius}km. Trying unassigned drivers...");

            $drivers = $driverRequestService->getNearbyAvailableDriversPublic(
                $this->vendorId,
                $this->lat,
                $this->lng,
                false,
                $this->currentRadius
            );
        }

        // If drivers found, send requests
        if ($drivers->isNotEmpty()) {
            Log::info("Found {$drivers->count()} drivers at {$this->currentRadius}km for {$this->requestType}");

            $expiresAt = now()->addMinutes(DriverRequestService::REQUEST_TIMEOUT);
            $requestsSent = 0;

            foreach ($drivers as $driver) {
                $exists = DriverRequest::where('order_id', $this->order->id)
                    ->where('driver_id', $driver->id)
                    ->where('request_type', $this->requestType)
                    ->exists();

                if ($exists) {
                    Log::debug("Request already exists for driver {$driver->id}, skipping");
                    continue;
                }

                try {
                    DriverRequest::create([
                        'order_id' => $this->order->id,
                        'driver_id' => $driver->id,
                        'request_type' => $this->requestType,
                        'status' => 'PENDING',
                        'expires_at' => $expiresAt,
                    ]);

                    $driverRequestService->sendDriverNotificationPublic($driver, $this->order, $this->requestType);
                    $requestsSent++;

                } catch (\Exception $e) {
                    Log::error("Failed to create request for driver {$driver->id}: {$e->getMessage()}");
                }
            }

            Log::info("Sent {$requestsSent} new requests at {$this->currentRadius}km radius");

            // Update cache - waiting for responses
            Cache::put($cacheKey, [
                'order_id' => $this->order->id,
                'order_code' => $this->order->order_code,
                'request_type' => $this->requestType,
                'current_radius' => $this->currentRadius,
                'attempt_number' => $this->attemptNumber,
                'status' => 'waiting',
                'drivers_found' => $drivers->count(),
                'requests_sent' => $requestsSent,
                'last_activity' => now(),
                'next_check_at' => now()->addSeconds(self::WAIT_TIME_SECONDS),
                'started_at' => Cache::get($cacheKey)['started_at'] ?? now(),
            ], now()->addMinutes(30));

            $nextRadius = min($this->currentRadius + self::RADIUS_INCREMENT, self::MAX_RADIUS);

            if ($nextRadius <= self::MAX_RADIUS) {
                self::dispatch(
                    $this->order,
                    $this->requestType,
                    $this->lat,
                    $this->lng,
                    $this->vendorId,
                    $nextRadius,  // ← Expands to NEXT radius
                    $this->attemptNumber + 1
                )->delay(now()->addSeconds(self::WAIT_TIME_SECONDS));
            }

        } else {
            // No drivers at this radius, expand
            $nextRadius = $this->currentRadius + self::RADIUS_INCREMENT;

            if ($nextRadius <= self::MAX_RADIUS) {
                Log::info("No drivers found at {$this->currentRadius}km. Expanding to {$nextRadius}km");

                // Update cache - expanding radius
                Cache::put($cacheKey, [
                    'order_id' => $this->order->id,
                    'order_code' => $this->order->order_code,
                    'request_type' => $this->requestType,
                    'current_radius' => $this->currentRadius,
                    'attempt_number' => $this->attemptNumber,
                    'status' => 'expanding',
                    'next_radius' => $nextRadius,
                    'last_activity' => now(),
                    'started_at' => Cache::get($cacheKey)['started_at'] ?? now(),
                ], now()->addMinutes(20));

                self::dispatch(
                    $this->order,
                    $this->requestType,
                    $this->lat,
                    $this->lng,
                    $this->vendorId,
                    $nextRadius,
                    $this->attemptNumber + 1
                )->delay(now()->addSeconds(5));

            } else {
                Log::warning("No drivers found within {$this->currentRadius}km (max radius reached) for {$this->requestType} on order #{$this->order->order_code}");

                // Update cache - failed
                Cache::put($cacheKey, [
                    'order_id' => $this->order->id,
                    'order_code' => $this->order->order_code,
                    'request_type' => $this->requestType,
                    'current_radius' => $this->currentRadius,
                    'attempt_number' => $this->attemptNumber,
                    'status' => 'failed',
                    'last_activity' => now(),
                    'failed_at' => now(),
                    'started_at' => Cache::get($cacheKey)['started_at'] ?? now(),
                ], now()->addMinutes(20));

                $this->notifyNoDriversAvailable();
            }
        }
    }

    protected function isDriverAlreadyAssigned()
    {
        if ($this->requestType === 'PICKUP') {
            return !is_null($this->order->pickup_driver_id);
        } else {
            return !is_null($this->order->delivery_driver_id);
        }
    }

    protected function hasAcceptedDriver()
    {
        return DriverRequest::where('order_id', $this->order->id)
            ->where('request_type', $this->requestType)
            ->where('status', 'ACCEPTED')
            ->exists();
    }

    protected function notifyNoDriversAvailable()
    {
        Log::error("NO DRIVERS AVAILABLE for {$this->requestType} on order #{$this->order->order_code}");

        try {
            $controller = new \App\Http\Controllers\Controller();
            $data = [
                "title" => "No Drivers Available - Order #{$this->order->order_code}",
                "message" => "No drivers found within " . self::MAX_RADIUS . "km for {$this->requestType}",
                "link" => "admin/order-details/{$this->order->id}",
            ];
            $controller->sendNotifications($data, 'admin');

            $customerData = [
                "title" => "Driver Search in Progress",
                "title_ar" => "جاري البحث عن سائق",
                "message" => "We're having difficulty finding a driver for your order #{$this->order->order_code}. Our team is working on it.",
                "message_ar" => "نواجه صعوبة في العثور على سائق لطلبك #{$this->order->order_code}. فريقنا يعمل على حل المشكلة.",
                "user" => $this->order->user,
                "order" => $this->order,
            ];
            $controller->sendNotifications($customerData, 'user');

        } catch (\Exception $e) {
            Log::error("Failed to send no-driver notifications: {$e->getMessage()}");
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("FindDriverForOrder job failed for order #{$this->order->order_code}: {$exception->getMessage()}");

        $cacheKey = "driver_search:{$this->order->id}:{$this->requestType}";
        Cache::put($cacheKey, [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'request_type' => $this->requestType,
            'status' => 'error',
            'error' => $exception->getMessage(),
            'last_activity' => now(),
        ], now()->addDays(7));
    }
}
