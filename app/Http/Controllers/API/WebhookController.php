<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\WebhookRideService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private WebhookRideService $service) {}


    public function handleLeajlakWebhook(Request $request)
    {
        try {
            Log::info('Leajlak Webhook Received', $request->all());

            // Validate the required values
            $request->validate([
                'id'     => 'required|integer',  // Order ID in our system
                'status' => 'required|string',
            ]);

            $orderId = $request->id;
            $status = $request->status;

            // Find the order
            $order = Order::find($orderId);

            if (! $order) {
                Log::warning("Leajlak Webhook: Order not found - ID {$orderId}");
                return response()->json([
                    'status'  => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // Handle different statuses
            $incomingStatus = strtolower(trim($status));

            if ($incomingStatus === 'delivered') {
                $lat = $request->input('lat', 0);
                $lng = $request->input('lng', 0);

                $order->update([
                    'status'        => 'DELIVERED',
                    'deliver_lat'   => $lat,
                    'deliver_lng'   => $lng,
                    'deliver_image' => null,
                ]);

                OrderTracking::firstOrCreate([
                    'order_id' => $order->id,
                    'status'   => 'DELIVERED',
                ]);

                // Process referral reward for first delivered order
                $referralService = new ReferralService();
                $referralService->processReferralReward($order);

                Log::info("Leajlak Webhook: Order #{$order->order_code} marked as DELIVERED");
            }

            // Also update order_driver if dsp_order_id is provided
            if ($request->has('dsp_order_id')) {
                $dspOrderId = $request->dsp_order_id;

                // Find the order_driver record
                $orderDriver = \App\Models\OrderDriver::where('external_ride_id', $dspOrderId)
                    ->where('provider', 'leajlak')
                    ->first();

                if ($orderDriver) {
                    // Update order_driver status
                    $orderDriver->update([
                        'status' => $status,
                        'time_changed' => now(),
                    ]);

                    Log::info("Leajlak Webhook: OrderDriver updated", [
                        'order_driver_id' => $orderDriver->id,
                        'dsp_order_id' => $dspOrderId,
                        'status' => $status
                    ]);
                } else {
                    // Create order_driver record if not exists
                    $orderDriver = \App\Models\OrderDriver::create([
                        'external_ride_id' => $dspOrderId,
                        'order_id' => $orderId,
                        'vendor_id' => $order->vendor_id,
                        'provider' => 'leajlak',
                        'status' => $status,
                        'time_changed' => now(),
                    ]);

                    Log::info("Leajlak Webhook: OrderDriver created", [
                        'order_driver_id' => $orderDriver->id,
                        'dsp_order_id' => $dspOrderId,
                        'status' => $status
                    ]);
                }

                // Handle driver information if provided
                if ($request->has('driver')) {
                    $driverData = $request->driver;

                    // Find or create external driver
                    $externalDriver = \App\Models\ExternalDriver::updateOrCreate(
                        [
                            'external_driver_id' => $driverData['id'] ?? null,
                            'provider' => 'leajlak'
                        ],
                        [
                            'name' => $driverData['name'] ?? 'Unknown Driver',
                            'phone' => $driverData['phone'] ?? null,
                            'email' => $driverData['email'] ?? null,
                            'profile_image' => $driverData['profile_image'] ?? null,
                        ]
                    );

                    // Update order_driver with driver_id
                    $orderDriver->update(['driver_id' => $externalDriver->id]);

                    // Update driver location if provided
                    if (isset($driverData['location'])) {
                        $location = $driverData['location'];
                        $orderDriver->update([
                            'start_lat' => $location['latitude'] ?? null,
                            'start_lng' => $location['longitude'] ?? null,
                        ]);
                    }
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Leajlak Webhook Error', [
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'request'   => $request->all(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function liveLocationLeajlak(Request $request)
    {
        try {
            $secret = config('services.leajlak.secret');
            if ($request->header('x-webhook-secret') !== $secret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Log::info('Leajlak Live Location Webhook:', $request->all());

            // Validate required fields
            $request->validate([
                'dsp_order_id' => 'required|integer',
                'driver_id' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $dspOrderId = $request->dsp_order_id;
            $driverId = $request->driver_id;
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            // Find the order_driver record using dsp_order_id as external_ride_id
            $orderDriver = \App\Models\OrderDriver::where('external_ride_id', $dspOrderId)
                ->where('provider', 'leajlak')
                ->first();

            if (!$orderDriver) {
                Log::warning("Leajlak Live Location: OrderDriver not found - dsp_order_id: {$dspOrderId}");
                return response()->json([
                    'status' => false,
                    'message' => 'OrderDriver not found',
                ], 404);
            }

            // Update driver location in order_driver
            $orderDriver->update([
                'start_lat' => $latitude,
                'start_lng' => $longitude,
                'time_changed' => now(),
            ]);

            // Update external driver location if driver exists
            if ($orderDriver->driver) {
                $orderDriver->driver->update([
                    'external_driver_id' => $driverId,
                ]);
            } else {
                // Create external driver if not exists
                $externalDriver = \App\Models\ExternalDriver::updateOrCreate(
                    [
                        'external_driver_id' => $driverId,
                        'provider' => 'leajlak'
                    ],
                    [
                        'name' => $request->driver_name ?? 'Unknown Driver',
                        'phone' => $request->driver_phone ?? null,
                        'email' => $request->driver_email ?? null,
                    ]
                );

                // Link driver to order_driver
                $orderDriver->update(['driver_id' => $externalDriver->id]);
            }

            Log::info("Leajlak Live Location: Location updated successfully", [
                'order_driver_id' => $orderDriver->id,
                'dsp_order_id' => $dspOrderId,
                'driver_id' => $driverId,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Location updated successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Leajlak Live Location Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Get order status from Leajlak API
     */
    public function getLeajlakOrderStatus(Request $request, $dspOrderId)
    {
        try {
            $orderData = \App\Services\LeajlakService::getOrderStatus($dspOrderId);

            if (!$orderData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to retrieve order status from Leajlak',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $orderData,
            ]);
        } catch (\Throwable $e) {
            Log::error('Get Leajlak Order Status Error', [
                'error' => $e->getMessage(),
                'dsp_order_id' => $dspOrderId,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Sync order status from Leajlak to local database
     */
    public function syncLeajlakOrderStatus(Request $request, $dspOrderId)
    {
        try {
            $success = \App\Services\LeajlakService::syncOrderStatus($dspOrderId);

            if (!$success) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to sync order status',
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order status synced successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Sync Leajlak Order Status Error', [
                'error' => $e->getMessage(),
                'dsp_order_id' => $dspOrderId,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    // fazaa webhook update ride
    public function updateRide(Request $request, ?string $provider = null, ?string $id = null)
    {
        $provider = 'fazaa';

        $payload = $request->all();
        Log::info('=----------------------------------------------------------------------');
        Log::info('Fazaa Webhook Update Ride', ['payload' => $payload]);
        Log::info('Fazaa Webhook Update Ride', ['provider' => $provider, 'id' => $id]);
        Log::info('=----------------------------------------------------------------------');
        // الحصول على external_ride_id (ride_id من Fazaa) من البيانات المرسلة
        $externalRideId = data_get($payload, 'ride_id') ??
                         data_get($payload, 'external_ride_id') ??
                         $id ?? null;

        Log::info('Fazaa Webhook externalRideId before if', ['externalRideId' => $externalRideId]);

        // التحقق من وجود provider_ride_id (order_id في نظامنا) - هذا مطلوب
        $orderId = data_get($payload, 'provider_ride_id');
        if (!$orderId) {
            return response()->json(['message' => 'provider_ride_id is required'], 400);
        }

        Log::info('Fazaa Webhook orderId (provider_ride_id)', ['orderId' => $orderId]);


        // 2) الحالة: من body أولاً ثم query (?status=)
        $incomingStatus = $payload['status'] ?? $request->query('status');
        Log::info('Fazaa Webhook incomingStatus before if', ['incomingStatus' => $incomingStatus]);
        if (!$incomingStatus) {
            return response()->json(['message' => 'status is required'], 422);
        }

        // بعض الأنظمة ترسلها كـ "{$accepted}" — ننظف الأقواس وعلامة $
        $incomingStatus = strtolower(trim($incomingStatus));
        $incomingStatus = preg_replace('/^\{\$?(.+)\}$/', '$1', $incomingStatus); // يحول "{$accepted}" -> "accepted"

        // 3) التوقيت لو لم يُرسل
        $timeChanged = $payload['time_changed'] ?? now()->toISOString();
        Log::info('Fazaa Webhook timeChanged before if', ['timeChanged' => $timeChanged]);

        // 4) نفّذ الخدمة كالمعتاد
        $this->service->handleIncomingRideUpdate(
            provider: $provider,
            externalRideId: $externalRideId ? (string) $externalRideId : null,
            incomingStatus: (string) $incomingStatus,
            timeChangedIso: (string) $timeChanged,
            payload: $payload
        );

        //         // 5)  if FINISHED or COMPLETED -> update order status to ARRIVED
        if (in_array($incomingStatus, ['finished', 'completed'], true)) {
            try {

                // try to get order id from payload (if the provider sends it)
                $orderId = $payload['provider_ride_id'];

                if ($orderId) {
                    // get the order to update the status
                    $order = \App\Models\Order::find($orderId);

                    if ($order) {
                        $currentStatus = $order->status;
                        $newStatus = null;

                        // check the current status and determine the new status
                        if ($currentStatus === 'ON_THE_WAY_FOR_PICKUP') {
                            $newStatus = 'ARRIVED';
                        } elseif ($currentStatus === 'ON_THE_WAY_TO_PARTNER') {
                            $newStatus = 'DELIVERED';
                        }

                        // update the status if a new status is determined
                        if ($newStatus) {
                            $order->update(['status' => $newStatus]);

                            // Process referral reward if order is delivered
                            if ($newStatus === 'DELIVERED') {
                                $referralService = new ReferralService();
                                $referralService->processReferralReward($order);
                            }

                            Log::info('Order status updated after ride completion', [
                                'order_id' => $orderId,
                                'provider' => $provider,
                                'external_ride_id' => $externalRideId,
                                'incoming_status' => $incomingStatus,
                                'previous_status' => $currentStatus,
                                'new_status' => $newStatus,
                            ]);
                        } else {
                            Log::info('Order status not updated - current status does not match required conditions', [
                                'order_id' => $orderId,
                                'provider' => $provider,
                                'external_ride_id' => $externalRideId,
                                'incoming_status' => $incomingStatus,
                                'current_status' => $currentStatus,
                            ]);
                        }
                    } else {
                        Log::warning('Order not found for status update', [
                            'order_id' => $orderId,
                            'provider' => $provider,
                            'external_ride_id' => $externalRideId,
                        ]);
                    }
                } else {
                    // we couldn't link the ride to any order
                    Log::warning('No matching order found for finished/completed ride', [
                        'provider' => $provider,
                        'external_ride_id' => $externalRideId,
                        'incoming_status' => $incomingStatus,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to set order status to ARRIVED', [
                    'error' => $e->getMessage(),
                    'provider' => $provider,
                    'external_ride_id' => $externalRideId,
                ]);
                // return 200 because the webhook provider usually expects success, but we saved the error in the log
            }
        }


        return response()->json(['ok' => true], 200);
    }
}
