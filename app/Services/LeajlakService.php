<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeajlakService
{
    public static function sendOrderToLeajlak($order): bool
    {
        try {
            // حمّل العلاقات إن لم تكن محمّلة
            $order->loadMissing(['user', 'deliveryAddress']);

            // لو علاقة user محمّلة سابقًا ولكن ناقصة الأعمدة، أعد تحميلها بدون 'name'
            if (
                $order->relationLoaded('user') &&
                !isset($order->user->first_name) &&
                !isset($order->user->last_name)
            ) {
                $order->unsetRelation('user');
                $order->load(['user:id,phone,first_name,last_name']);
            }

            $user    = $order->user;
            $address = $order->deliveryAddress;

            if (! $address) {
                Log::warning('Leajlak: Missing delivery address relation', [
                    'order_id' => $order->id,
                ]);
                throw new \Exception('Missing fields: address relation');
            }

            // ابنِ الاسم من first/last مع فولباك للهاتف
            $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if ($name === '') {
                $name = $user->phone ?? '';
            }

            // عنوان نصي مرتب
            $parts = [
                $address->area        ?? null,
                $address->street_no   ?? null,
                $address->building    ?? null,
                $address->floor       ?? null,
                $address->appartment  ?? null,
            ];
            $parts = array_values(array_filter(array_map(function ($v) {
                if ($v === null) return null;
                if (is_string($v)) {
                    $v = trim($v);
                    return $v === '' ? null : $v;
                }
                return $v;
            }, $parts)));

            $fullAddress = implode(', ', $parts);

            // تحققات دقيقة
            $missing = [];
            if ($name === '' || $name === ' ') {
                $missing[] = 'recipient name';
            }
            $lat = $address->lat ?? null;
            $lng = $address->lng ?? null;
            if ($fullAddress === '') {
                $missing[] = 'address';
            }
            if (empty($lat) || empty($lng)) {
                $missing[] = 'coordinates';
            }

            if ($missing) {
                Log::warning('Leajlak: Missing required fields', [
                    'missing'  => $missing,
                    'order_id' => $order->id,
                    'name'     => $name,
                    'address'  => $fullAddress,
                    'lat'      => $lat,
                    'lng'      => $lng,
                ]);
                throw new \Exception('Missing fields: ' . implode(', ', $missing));
            }

            // بناء الـ payload
            $payload = [
                "id"                    => (string) $order->id,
                "internal_reference_id" => (string) $order->vendor_id,
                "delivery_details"      => [
                    "name"       => $name,
                    "phone"      => $user->phone,
                    "coordinate" => [
                        "latitude"  => (float) $lat,
                        "longitude" => (float) $lng,
                    ],
                    "address" => $fullAddress,
                ],
                "order" => [
                    "payment_type" => 0,
                    "total"        => (float) $order->grand_total,
                    "notes"        => $order->instructions ?? '',
                ],
            ];

            // الإرسال (LIVE)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('LEAJLAK_API_TOKEN_LIVE'),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->post('https://app.leajlak.com/api/clean-basket/orders', $payload);

            if (! $response->successful()) {
                Log::error('Leajlak API failed', [
                    'order_id' => $order->id,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                    'payload'  => $payload,
                ]);
                return false;
            }

            $responseData = $response->json();
            
            Log::info('Order sent to Leajlak successfully.', [
                'order_id' => $order->id,
                'payload'  => $payload,
                'response' => $responseData,
            ]);

            // Create OrderDriver record with dsp_order_id as external_ride_id
            if (isset($responseData['dsp_order_id'])) {
                \App\Models\OrderDriver::create([
                    'external_ride_id' => $responseData['dsp_order_id'],
                    'order_id' => $order->id,
                    'vendor_id' => $order->vendor_id,
                    'provider' => 'leajlak',
                    'status' => 'sent_to_provider',
                    'time_changed' => now(),
                ]);

                Log::info('OrderDriver record created for Leajlak order', [
                    'order_id' => $order->id,
                    'dsp_order_id' => $responseData['dsp_order_id']
                ]);
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('LeajlakService Exception', [
                'error'    => $e->getMessage(),
                'order_id' => $order->id ?? null,
            ]);
            return false;
        }
    }

    /**
     * Get order status from Leajlak API
     */
    public static function getOrderStatus($dspOrderId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('LEAJLAK_API_TOKEN_LIVE'),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->get("https://staging.4ulogistic.com/api/clean-basket/orders/{$dspOrderId}");

            if (!$response->successful()) {
                Log::error('Leajlak API get order status failed', [
                    'dsp_order_id' => $dspOrderId,
                    'status'       => $response->status(),
                    'response'     => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            Log::info('Order status retrieved from Leajlak', [
                'dsp_order_id' => $dspOrderId,
                'response'     => $data,
            ]);

            return $data;

        } catch (\Throwable $e) {
            Log::error('LeajlakService getOrderStatus Exception', [
                'error'        => $e->getMessage(),
                'dsp_order_id' => $dspOrderId,
            ]);
            return null;
        }
    }

    /**
     * Sync order status from Leajlak to local database
     */
    public static function syncOrderStatus($dspOrderId): bool
    {
        try {
            $orderData = self::getOrderStatus($dspOrderId);
            
            if (!$orderData) {
                return false;
            }

            // Find the order_driver record
            $orderDriver = \App\Models\OrderDriver::where('external_ride_id', $dspOrderId)
                ->where('provider', 'leajlak')
                ->first();

            if (!$orderDriver) {
                Log::warning("OrderDriver not found for sync - dsp_order_id: {$dspOrderId}");
                return false;
            }

            // Update order_driver status
            $orderDriver->update([
                'status' => $orderData['status'] ?? 'unknown',
                'time_changed' => now(),
            ]);

            // Handle driver information if provided
            if (isset($orderData['driver'])) {
                $driverData = $orderData['driver'];
                
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

            Log::info("Order status synced successfully", [
                'dsp_order_id' => $dspOrderId,
                'status' => $orderData['status'] ?? 'unknown'
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error('LeajlakService syncOrderStatus Exception', [
                'error'        => $e->getMessage(),
                'dsp_order_id' => $dspOrderId,
            ]);
            return false;
        }
    }
}
