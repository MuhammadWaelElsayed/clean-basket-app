<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverRequest;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Jobs\FindDriverForOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DriverRequestService extends Controller
{
    const REQUEST_TIMEOUT = 180; // minutes
    const MAX_DRIVERS_PER_BATCH = 10;
    const MINUTES_BEFORE_PICKUP = 30; // Dispatch job 30 minutes before scheduled time

    /**
     * Calculate when to dispatch the job (30 minutes before the scheduled time)
     *
     * @param string $date Date in format 'Y-m-d' (e.g., '2026-01-20')
     * @param string $time Time in format 'HH:MM - HH:MM' (range) or 'HH:MM' (single time)
     * @return Carbon|null Returns the scheduled dispatch time or null if invalid
     */
    protected function calculateScheduledTime($date, $time)
    {
        if (!$date || !$time) {
            return null;
        }

        try {
            $startTime = null;

            // Check if time contains a dash (range format)
            if (strpos($time, '-') !== false) {
                // Time format is a range like "07:00 - 08:00"
                // Extract the start time (first part before the dash)
                $timeParts = explode('-', $time);

                if (empty($timeParts[0])) {
                    Log::warning("Invalid time range format - no start time found: {$time}");
                    return null;
                }

                $startTime = trim($timeParts[0]);
            } else {
                // Time is a single value like "07:00"
                $startTime = trim($time);
            }

            // Validate time format (HH:MM)
            if (!preg_match('/^\d{1,2}:\d{2}$/', $startTime)) {
                Log::warning("Invalid time format: {$startTime}");
                return null;
            }

            // Combine date and start time
            $dateTime = Carbon::parse($date . ' ' . $startTime);

            // Subtract 30 minutes to get when to dispatch the job
            $scheduledTime = $dateTime->copy()->subMinutes(self::MINUTES_BEFORE_PICKUP);

            Log::info("Calculated scheduled time: {$scheduledTime->toDateTimeString()} (30 min before {$dateTime->toDateTimeString()})");

            return $scheduledTime;
        } catch (\Exception $e) {
            Log::error("Failed to parse date/time (date: {$date}, time: {$time}): {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Store upcoming job info in cache
     */
    protected function cacheUpcomingJob(Order $order, $requestType, $scheduledTime)
    {
        $cacheKey = "upcoming_job:{$order->id}:{$requestType}";

        // Clone the scheduledTime to avoid mutation
        $expiresAt = $scheduledTime->copy()->addHours(1);

        Cache::put($cacheKey, [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'request_type' => $requestType,
            'scheduled_at' => $scheduledTime->toDateTimeString(),
            'status' => 'scheduled',
            'created_at' => now()->toDateTimeString(),
        ], $expiresAt);
    }

    /**
     * Get all upcoming scheduled jobs
     */
    public function getUpcomingJobs($orderId = null)
    {
        $allKeys = Cache::get('all_upcoming_jobs', []);
        $upcomingJobs = [];

        foreach ($allKeys as $key) {
            $job = Cache::get($key);

            if ($job && isset($job['status']) && $job['status'] === 'scheduled') {
                // If specific order, filter by order_id
                if ($orderId && $job['order_id'] != $orderId) {
                    continue;
                }

                try {
                    $scheduledAt = Carbon::parse($job['scheduled_at']);

                    // Only include future jobs
                    if ($scheduledAt->isFuture()) {
                        $job['time_until_start'] = $scheduledAt->diffForHumans();
                        $job['time_until_start_seconds'] = now()->diffInSeconds($scheduledAt);
                        $upcomingJobs[] = $job;
                    } else {
                        // Remove expired job from tracking
                        $this->removeFromTracking($key);
                        Cache::forget($key);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to parse scheduled_at for job: {$e->getMessage()}");
                }
            }
        }

        // Sort by scheduled time (earliest first)
        usort($upcomingJobs, function($a, $b) {
            return $a['scheduled_at'] <=> $b['scheduled_at'];
        });

        return $upcomingJobs;
    }

    /**
     * Track upcoming job keys in a master list
     */
    protected function trackUpcomingJob($cacheKey, $expiresAt)
    {
        $allKeys = Cache::get('all_upcoming_jobs', []);

        if (!in_array($cacheKey, $allKeys)) {
            $allKeys[] = $cacheKey;
            // Store with same expiration as the longest job
            Cache::put('all_upcoming_jobs', $allKeys, $expiresAt);
        }
    }

    /**
     * Remove a key from tracking list
     */
    protected function removeFromTracking($cacheKey)
    {
        $allKeys = Cache::get('all_upcoming_jobs', []);
        $allKeys = array_values(array_filter($allKeys, function($key) use ($cacheKey) {
            return $key !== $cacheKey;
        }));
        Cache::put('all_upcoming_jobs', $allKeys, now()->addDays(7));
    }

    /**
     * Send PICKUP request to nearby drivers (initiates background job)
     */
    public function sendPickupRequestToDrivers(Order $order, $vendorId, $lat, $lng)
    {
        Log::info("Initiating PICKUP driver search job for order #{$order->order_code}");

        // Calculate when to dispatch the job (30 minutes before pickup time)
        $scheduledTime = $this->calculateScheduledTime($order->pickup_date, $order->pickup_time);

        if ($scheduledTime && $scheduledTime->isFuture()) {
            // Cache the upcoming job info
            $this->cacheUpcomingJob($order, 'PICKUP', $scheduledTime);
            $cacheKey = "upcoming_job:{$order->id}:PICKUP";
            // Clone for tracking to avoid mutation
            $this->trackUpcomingJob($cacheKey, $scheduledTime->copy()->addHours(1));

            FindDriverForOrder::dispatch(
                $order,
                'PICKUP',
                $lat,
                $lng,
                $vendorId
            )->delay($scheduledTime);

            Log::info("PICKUP driver search scheduled for {$scheduledTime->toDateTimeString()} (30 min before pickup)");

            return [
                'success' => true,
                'message' => 'Driver search scheduled for ' . $scheduledTime->diffForHumans(),
                'scheduled_at' => $scheduledTime->toDateTimeString(),
            ];
        } else {
            // If time is in the past or not set, dispatch immediately
            FindDriverForOrder::dispatch(
                $order,
                'PICKUP',
                $lat,
                $lng,
                $vendorId
            );

            Log::info("PICKUP driver search dispatched immediately (scheduled time in past or not set)");

            return [
                'success' => true,
                'message' => 'Driver search initiated immediately',
            ];
        }
    }

    /**
     * Send DELIVERY request to nearby drivers (initiates background job)
     */
    public function sendDeliveryRequestToDrivers(Order $order, $vendorId, $lat, $lng)
    {
        Log::info("Initiating DELIVERY driver search job for order #{$order->order_code}");

        // Calculate when to dispatch the job (30 minutes before dropoff time)
        $scheduledTime = $this->calculateScheduledTime($order->dropoff_date, $order->dropoff_time);

        if ($scheduledTime && $scheduledTime->isFuture()) {
            // Cache the upcoming job info
            $this->cacheUpcomingJob($order, 'DELIVERY', $scheduledTime);
            $cacheKey = "upcoming_job:{$order->id}:DELIVERY";
            // Clone for tracking to avoid mutation
            $this->trackUpcomingJob($cacheKey, $scheduledTime->copy()->addHours(1));

            FindDriverForOrder::dispatch(
                $order,
                'DELIVERY',
                $lat,
                $lng,
                $vendorId
            )->delay($scheduledTime);

            Log::info("DELIVERY driver search scheduled for {$scheduledTime->toDateTimeString()} (30 min before dropoff)");

            return [
                'success' => true,
                'message' => 'Delivery driver search scheduled for ' . $scheduledTime->diffForHumans(),
                'scheduled_at' => $scheduledTime->toDateTimeString(),
            ];
        } else {
            // If time is in the past or not set, dispatch immediately
            FindDriverForOrder::dispatch(
                $order,
                'DELIVERY',
                $lat,
                $lng,
                $vendorId
            );

            Log::info("DELIVERY driver search dispatched immediately (scheduled time in past or not set)");

            return [
                'success' => true,
                'message' => 'Delivery driver search initiated immediately',
            ];
        }
    }

    /**
     * Cancel an upcoming scheduled job
     */
    public function cancelUpcomingJob(Order $order, $requestType)
    {
        $cacheKey = "upcoming_job:{$order->id}:{$requestType}";

        $job = Cache::get($cacheKey);

        if ($job) {
            Cache::forget($cacheKey);

            // Remove from tracking list
            $this->removeFromTracking($cacheKey);

            Log::info("Cancelled upcoming {$requestType} job for order #{$order->order_code}");

            return [
                'success' => true,
                'message' => 'Upcoming job cancelled successfully',
            ];
        }

        return [
            'success' => false,
            'message' => 'No upcoming job found',
        ];
    }

    /**
     * Get nearby available drivers (made public for job access)
     */
    public function getNearbyAvailableDriversPublic($vendorId, $lat, $lng, $assignedOnly = true, $radius = 10)
    {
        $vendor = Vendor::find($vendorId);

        if (!$vendor) {
            Log::warning("Vendor not found: {$vendorId}");
            return collect();
        }

        $drivers = Driver::where('status', 1)
//            ->where('is_online', 1)
//            ->where('is_free', 1)
            ->whereNull('deleted_at')
            ->when($assignedOnly, function($query) use ($vendorId) {
                $query->whereHas('vendors', function($query) use ($vendorId) {
                    $query->where('vendors.id', $vendorId);
                });
            })
            ->selectRaw("drivers.*,
                6371 * acos(
                    cos(radians(?)) * cos(radians(drivers.lat)) * cos(radians(drivers.lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(drivers.lat))
                ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->get();

        // Filter drivers to only include those within the vendor's service area
        $driversInArea = $drivers->filter(function($driver) use ($vendor) {
            // Check if vendor has areas defined
            if (empty($vendor->areas)) {
                return true; // If no areas defined, include all drivers
            }
            return $this->isPointInPolygon($vendor->areas, $driver->lat, $driver->lng);
        });

        return $driversInArea
            ->sortBy('distance')
            ->take(self::MAX_DRIVERS_PER_BATCH)
            ->values();
    }

    /**
     * Check if a point is inside a polygon (vendor service area)
     */
    public function isPointInPolygon($polygon, $lat, $lng)
    {
        if (empty($polygon)) {
            return true;
        }

        // If polygon is stored as JSON string, decode it
        if (is_string($polygon)) {
            $polygon = json_decode($polygon, true);
        }

        // Ensure polygon is an array of coordinates
        if (!is_array($polygon) || empty($polygon)) {
            return true;
        }

        $vertices = count($polygon);
        $isInside = false;

        for ($i = 0, $j = $vertices - 1; $i < $vertices; $j = $i++) {
            $xi = $polygon[$i]['lat'] ?? $polygon[$i][0] ?? 0;
            $yi = $polygon[$i]['lng'] ?? $polygon[$i][1] ?? 0;
            $xj = $polygon[$j]['lat'] ?? $polygon[$j][0] ?? 0;
            $yj = $polygon[$j]['lng'] ?? $polygon[$j][1] ?? 0;

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $isInside = !$isInside;
            }
        }

        return $isInside;
    }

    /**
     * Send notification to driver (made public for job access)
     */
    public function sendDriverNotificationPublic(Driver $driver, Order $order, $requestType)
    {
        if ($requestType === 'PICKUP') {
            $data = [
                "title" => "New Pickup Request",
                "title_ar" => "طلب استلام جديد",
                "message" => "You have a new PICKUP request. Order #{$order->order_code}",
                "message_ar" => "لديك طلب استلام جديد. طلب رقم #{$order->order_code}",
                "user" => $driver,
                "order" => $order,
            ];
        } else {
            $data = [
                "title" => "New Delivery Request",
                "title_ar" => "طلب توصيل جديد",
                "message" => "You have a new DELIVERY request. Order #{$order->order_code}",
                "message_ar" => "لديك طلب توصيل جديد. طلب رقم #{$order->order_code}",
                "user" => $driver,
                "order" => $order,
            ];
        }

        $this->sendNotifications($data, 'driver');
    }

    /**
     * Accept order by driver
     */
    public function acceptOrder(DriverRequest $driverRequest, Driver $driver)
    {
        if ($driverRequest->status !== 'PENDING') {
            return [
                'success' => false,
                'message' => 'This request is no longer available',
            ];
        }

        // Check if expired
        if ($driverRequest->expires_at && $driverRequest->expires_at < now()) {
            $driverRequest->update([
                'status' => 'EXPIRED',
                'responded_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'This request has expired',
            ];
        }

        $order = $driverRequest->order;
        $requestType = $driverRequest->request_type;

        // Check if order already has driver for this phase (race condition protection)
        if ($requestType === 'PICKUP' && $order->pickup_driver_id && $order->pickup_driver_id !== $driver->id) {
            return [
                'success' => false,
                'message' => 'This pickup has already been accepted by another driver',
            ];
        }

        if ($requestType === 'DELIVERY' && $order->delivery_driver_id && $order->delivery_driver_id !== $driver->id) {
            return [
                'success' => false,
                'message' => 'This delivery has already been accepted by another driver',
            ];
        }

        try {
            \DB::beginTransaction();

            // Mark request as accepted
            $driverRequest->update([
                'status' => 'ACCEPTED',
                'responded_at' => now(),
            ]);

            // Assign driver based on request type
            if ($requestType === 'PICKUP') {
                $order->update(['pickup_driver_id' => $driver->id]);
            } else {
                $order->update(['delivery_driver_id' => $driver->id]);
            }

            // Mark driver as busy
            $driver->update(['is_free' => 0]);

            // Reject all other pending requests for this order and type
            DriverRequest::where('order_id', $order->id)
                ->where('request_type', $requestType)
                ->where('id', '!=', $driverRequest->id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'REJECTED',
                    'responded_at' => now(),
                    'rejection_reason' => 'Order accepted by another driver',
                ]);

            $this->sendAcceptanceNotifications($order, $driver, $requestType);

            \DB::commit();

            return [
                'success' => true,
                'message' => ucfirst(strtolower($requestType)) . ' accepted successfully',
                'order' => $order->fresh(['user', 'vendor']),
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("Failed to accept {$requestType}: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Failed to accept request. Please try again.',
            ];
        }
    }

    /**
     * Reject order by driver
     */
    public function rejectOrder(DriverRequest $driverRequest, $reason = null)
    {
        if ($driverRequest->status !== 'PENDING') {
            return [
                'success' => false,
                'message' => 'This request is no longer available',
            ];
        }

        $driverRequest->update([
            'status' => 'REJECTED',
            'responded_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => 'Order request rejected',
        ];
    }

    /**
     * Send notifications after order acceptance
     */
    protected function sendAcceptanceNotifications(Order $order, Driver $driver, $requestType)
    {
        $phase = ucfirst(strtolower($requestType));

        try {
            // Notify customer
            $customerData = [
                "title" => "Driver Assigned for {$phase}",
                "title_ar" => "تم تعيين سائق لـ" . ($requestType === 'PICKUP' ? 'الاستلام' : 'التوصيل'),
                "message" => "Driver {$driver->name} has been assigned for {$phase} - Order #{$order->order_code}",
                "message_ar" => "تم تعيين السائق {$driver->name} لـ" . ($requestType === 'PICKUP' ? 'استلام' : 'توصيل') . " طلبك #{$order->order_code}",
                "user" => $order->user,
                "order" => $order,
            ];
            $this->sendNotifications($customerData, 'user');

            // Notify vendor
            if ($order->vendor) {
                $vendorData = [
                    "title" => "Driver Assigned for {$phase}",
                    "title_ar" => "تم تعيين سائق لـ" . ($requestType === 'PICKUP' ? 'الاستلام' : 'التوصيل'),
                    "message" => "Driver {$driver->name} assigned for {$phase} - Order #{$order->order_code}",
                    "message_ar" => "تم تعيين السائق {$driver->name} لـ" . ($requestType === 'PICKUP' ? 'الاستلام' : 'التوصيل') . " - طلب #{$order->order_code}",
                    "user" => $order->vendor,
                    "order" => $order,
                ];
                $this->sendNotifications($vendorData, 'vendor');
            }

            // Notify admin
            $adminData = [
                "title" => "Order #{$order->order_code} - {$phase} Driver Assigned",
                "message" => "Driver {$driver->name} accepted {$phase} for order #{$order->order_code}",
                "link" => "admin/order-details/{$order->id}",
            ];
            $this->sendNotifications($adminData, 'admin');

        } catch (\Exception $e) {
            Log::error("Failed to send acceptance notifications: {$e->getMessage()}");
        }
    }

    /**
     * Expire old pending requests (run via scheduled command)
     */
    public function expireOldRequests()
    {
        $expired = DriverRequest::where('status', 'PENDING')
            ->where('expires_at', '<', now())
            ->update([
                'status' => 'EXPIRED',
                'responded_at' => now(),
            ]);

        Log::info("Expired {$expired} old driver requests");

        return $expired;
    }

    /**
     * Check if order has pending requests for specific type
     */
    public function hasPendingRequests(Order $order, $requestType = null)
    {
        $query = DriverRequest::where('order_id', $order->id)
            ->where('status', 'PENDING');

        if ($requestType) {
            $query->where('request_type', $requestType);
        }

        return $query->exists();
    }

    /**
     * Get order request statistics by type
     */
    public function getOrderRequestStats(Order $order, $requestType = null)
    {
        $query = DriverRequest::where('order_id', $order->id);

        if ($requestType) {
            $query->where('request_type', $requestType);
        }

        $requests = $query->get();

        return [
            'total_sent' => $requests->count(),
            'pending' => $requests->where('status', 'PENDING')->count(),
            'accepted' => $requests->where('status', 'ACCEPTED')->count(),
            'rejected' => $requests->where('status', 'REJECTED')->count(),
            'expired' => $requests->where('status', 'EXPIRED')->count(),
        ];
    }

    /**
     * Trigger delivery phase (called when order is READY_TO_DELIVER)
     */
    public function triggerDeliveryPhase(Order $order)
    {
        // Check if delivery requests already sent
        if ($this->hasPendingRequests($order, 'DELIVERY')) {
            return [
                'success' => false,
                'message' => 'Delivery requests already pending',
            ];
        }

        // Check if delivery driver already assigned
        if ($order->delivery_driver_id) {
            return [
                'success' => false,
                'message' => 'Delivery driver already assigned',
            ];
        }

        $address = $order->address; // or deliveryAddress depending on your setup

        if (!$address) {
            return [
                'success' => false,
                'message' => 'Delivery address not found',
            ];
        }

        // Send delivery requests via background job
        return $this->sendDeliveryRequestToDrivers(
            $order,
            $order->vendor_id,
            $address->lat,
            $address->lng
        );
    }

    /**
     * Cancel all pending requests for an order
     */
    public function cancelPendingRequests(Order $order, $requestType = null)
    {
        $query = DriverRequest::where('order_id', $order->id)
            ->where('status', 'PENDING');

        if ($requestType) {
            $query->where('request_type', $requestType);
        }

        $updated = $query->update([
            'status' => 'REJECTED',
            'responded_at' => now(),
            'rejection_reason' => 'Order cancelled',
        ]);

        Log::info("Cancelled {$updated} pending requests for order #{$order->order_code}");

        return $updated;
    }

    /**
     * Get all requests for a specific order
     */
    public function getOrderRequests(Order $order, $requestType = null)
    {
        $query = DriverRequest::where('order_id', $order->id)
            ->with('driver');

        if ($requestType) {
            $query->where('request_type', $requestType);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get active requests for a driver
     */
    public function getDriverActiveRequests(Driver $driver)
    {
        return DriverRequest::where('driver_id', $driver->id)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->with(['order.user', 'order.address'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get driver request history
     */
    public function getDriverRequestHistory(Driver $driver, $limit = 50)
    {
        return DriverRequest::where('driver_id', $driver->id)
            ->with(['order.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Manually assign driver to order (admin function)
     */
    public function manuallyAssignDriver(Order $order, Driver $driver, $requestType)
    {
        try {
            \DB::beginTransaction();

            // Create accepted request
            $request = DriverRequest::create([
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'request_type' => $requestType,
                'status' => 'ACCEPTED',
                'expires_at' => now()->addMinutes(self::REQUEST_TIMEOUT),
                'responded_at' => now(),
            ]);

            // Assign driver based on request type
            if ($requestType === 'PICKUP') {
                $order->update(['pickup_driver_id' => $driver->id]);
            } else {
                $order->update(['delivery_driver_id' => $driver->id]);
            }

            // Mark driver as busy
            $driver->update(['is_free' => 0]);

            // Cancel other pending requests
            DriverRequest::where('order_id', $order->id)
                ->where('request_type', $requestType)
                ->where('id', '!=', $request->id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'REJECTED',
                    'responded_at' => now(),
                    'rejection_reason' => 'Manually assigned to another driver',
                ]);

            $this->sendAcceptanceNotifications($order, $driver, $requestType);

            \DB::commit();

            Log::info("Manually assigned driver {$driver->id} to order #{$order->order_code} for {$requestType}");

            return [
                'success' => true,
                'message' => 'Driver assigned successfully',
                'order' => $order->fresh(),
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("Failed to manually assign driver: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Failed to assign driver. Please try again.',
            ];
        }
    }

    /**
     * Resend requests to drivers (retry mechanism)
     */
    public function resendRequestsToDrivers(Order $order, $requestType)
    {
        // Cancel existing pending requests
        $this->cancelPendingRequests($order, $requestType);

        // Determine coordinates based on request type
        $address = $order->address;

        if (!$address) {
            return [
                'success' => false,
                'message' => 'Address not found',
            ];
        }

        // Restart the job
        if ($requestType === 'PICKUP') {
            return $this->sendPickupRequestToDrivers(
                $order,
                $order->vendor_id,
                $address->lat,
                $address->lng
            );
        } else {
            return $this->sendDeliveryRequestToDrivers(
                $order,
                $order->vendor_id,
                $address->lat,
                $address->lng
            );
        }
    }
}
