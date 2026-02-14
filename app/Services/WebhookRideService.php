<?php

namespace App\Services;

use App\Models\ExternalDriver;
use App\Models\OrderDriver;
use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class WebhookRideService
{
    /**
     * معالجة تحديثات الرحلة من الأنظمة الخارجية (Fazaa/Leajlak)
     *
     * المعرفات:
     * - order_id: رقم الطلب في نظامنا (يأتي من provider_ride_id)
     * - external_ride_id: ride_id الخارجي من نظام Fazaa
     * - provider: مزود الخدمة (fazaa/leajlak)
     */
    public function handleIncomingRideUpdate(
        string $provider,
        ?string $externalRideId, // ride_id الخارجي من نظام Fazaa
        string $incomingStatus,
        string $timeChangedIso,
        array $payload
    ): void {
        // 1) تطبيع الحالة
        $status = $this->mapStatus($incomingStatus);

        // 2) قيم اختيارية
        $startLat = Arr::get($payload, 'start_lat');
        $startLng = Arr::get($payload, 'start_long', Arr::get($payload, 'start_lng'));
        $endLat   = Arr::get($payload, 'end_lat');
        $endLng   = Arr::get($payload, 'end_long', Arr::get($payload, 'end_lng'));
        $tripCost = Arr::get($payload, 'trip_cost');

        // 3) السائق (اختياري)
        $driverBlock = Arr::get($payload, 'driver', []);
        $extDriverId = Arr::get($driverBlock, 'driver_id');
        $driverId    = null;

        if ($extDriverId) {
            // البحث عن السائق أولاً
            $driver = ExternalDriver::where('external_driver_id', (string)$extDriverId)->first();

            if (!$driver) {
                // السائق غير موجود، إنشاء سجل جديد
                $driver = ExternalDriver::create([
                    'external_driver_id' => (string)$extDriverId,
                    'name'               => Arr::get($driverBlock, 'name'),
                    'phone'              => Arr::get($driverBlock, 'phone'),
                    'email'              => Arr::get($driverBlock, 'email'),
                    'provider'           => $provider,
                    'profile_image'      => Arr::get($driverBlock, 'profile_image'),
                ]);
                Log::info('ExternalDriver created', [
                    'external_driver_id' => $extDriverId,
                    'name' => Arr::get($driverBlock, 'name'),
                    'phone' => Arr::get($driverBlock, 'phone'),
                    'email' => Arr::get($driverBlock, 'email'),
                    'provider' => $provider,
                    'profile_image' => Arr::get($driverBlock, 'profile_image'),
                ]);
            } else {
                // السائق موجود، تحديث البيانات إذا لزم الأمر
                $driver->update([
                    'name'          => Arr::get($driverBlock, 'name', $driver->name),
                    'phone'         => Arr::get($driverBlock, 'phone', $driver->phone),
                    'email'         => Arr::get($driverBlock, 'email', $driver->email),
                    'profile_image' => Arr::get($driverBlock, 'profile_image', $driver->profile_image),
                ]);
                Log::info('ExternalDriver updated', [
                    'external_driver_id' => $extDriverId,
                    'name' => Arr::get($driverBlock, 'name'),
                    'phone' => Arr::get($driverBlock, 'phone'),
                    'email' => Arr::get($driverBlock, 'email'),
                    'provider' => $provider,
                    'profile_image' => Arr::get($driverBlock, 'profile_image'),
                ]);
            }
            $driverId = $driver->id;
        }

        // 4) ابحث أولاً عن السجل برقم الطلب (provider_ride_id) + المزود
        $orderId = Arr::get($payload, 'provider_ride_id'); // رقم الطلب في نظامنا
        $row = null;

        if ($orderId) {
            $row = OrderDriver::where('provider', $provider)
                ->where('order_id', $orderId)
                ->first();
        }

        if ($row) {
            // ✅ موجود: حدّث الحالة و external_ride_id (ride_id من Fazaa)
            $row->status       = $status;
            $row->time_changed = $timeChangedIso;

            // تحديث external_ride_id (ride_id من Fazaa) إذا وُجد
            if ($externalRideId) {
                $row->external_ride_id = (string)$externalRideId;
            }

            if (!is_null($driverId)) {
                $row->driver_id = $driverId; // نحدّث السائق لو أُرسل فقط
            }

            // تحديث الإحداثيات إذا وُجدت
            if (!is_null($startLat)) $row->start_lat = $startLat;
            if (!is_null($startLng)) $row->start_lng = $startLng;
            if (!is_null($endLat)) $row->end_lat = $endLat;
            if (!is_null($endLng)) $row->end_lng = $endLng;
            if (!is_null($tripCost)) $row->trip_cost = $tripCost;

            $row->save();
            Log::info('OrderDriver record updated via order_id', [
                'order_id' => $orderId,
                'external_ride_id' => $externalRideId,
                'provider' => $provider,
                'status' => $status
            ]);
            return;
        }

        // 5) إذا لم نجد السجل، ابحث عن طريق external_ride_id (ride_id من Fazaa) كبديل
        if ($externalRideId) {
            $row = OrderDriver::where('provider', $provider)
                ->where('external_ride_id', (string)$externalRideId) // ride_id من Fazaa
                ->first();

            if ($row) {
                // ✅ وجدنا السجل برقم الرحلة، نحدّث الحالة
                $row->status       = $status;
                $row->time_changed = $timeChangedIso;

                if (!is_null($driverId)) {
                    $row->driver_id = $driverId;
                }

                // تحديث الإحداثيات إذا وُجدت
                if (!is_null($startLat)) $row->start_lat = $startLat;
                if (!is_null($startLng)) $row->start_lng = $startLng;
                if (!is_null($endLat)) $row->end_lat = $endLat;
                if (!is_null($endLng)) $row->end_lng = $endLng;
                if (!is_null($tripCost)) $row->trip_cost = $tripCost;

                $row->save();
                Log::info('OrderDriver record updated via external_ride_id', [
                    'order_id' => $orderId,
                    'external_ride_id' => $externalRideId,
                    'provider' => $provider,
                    'status' => $status
                ]);
                return;
            }
        }

        // ❌ غير موجود: أنشئ سجل جديد (ونسجّل ما توفر فقط)
        $new = new OrderDriver();
        $new->provider         = $provider;
        $new->status           = $status;
        $new->time_changed     = $timeChangedIso;

        // إضافة order_id إذا وُجد
        if ($orderId) {
            $new->order_id = $orderId;
        }

        // إضافة external_ride_id (ride_id من Fazaa) إذا وُجد
        if ($externalRideId) {
            $new->external_ride_id = (string)$externalRideId;
        }

        if (!is_null($driverId)) $new->driver_id = $driverId;
        if (!is_null($startLat)) $new->start_lat = $startLat;
        if (!is_null($startLng)) $new->start_lng = $startLng;
        if (!is_null($endLat))   $new->end_lat   = $endLat;
        if (!is_null($endLng))   $new->end_lng   = $endLng;
        if (!is_null($tripCost)) $new->trip_cost = $tripCost;

        Log::info('OrderDriver created', [
            'external_ride_id' => $externalRideId,
            'order_id' => $orderId,
            'provider' => $provider,
            'status' => $status,
            'time_changed' => $timeChangedIso,
        ]);
        $new->save();
        Log::info('OrderDriver saved', [
            'external_ride_id' => $externalRideId,
            'order_id' => $orderId,
            'provider' => $provider,
            'status' => $status,
            'time_changed' => $timeChangedIso,
        ]);
    }

    /**
     * إنشاء سجل جديد في OrderDriver من EditOrder
     *
     * @param int $orderId رقم الطلب
     * @param string $provider مزود الخدمة (fazaa/leajlak)
     * @param string $status حالة الرحلة
     * @param array $additionalData بيانات إضافية
     * @return OrderDriver|null
     */
    public function createOrderDriverRecord(int $orderId, string $provider, string $status = 'NEW', array $additionalData = []): ?OrderDriver
    {
        try {
            // التحقق من وجود سجل مسبق
            $existingRecord = OrderDriver::where('provider', $provider)
                ->where('order_id', $orderId)
                ->first();

            if ($existingRecord) {
                Log::info('OrderDriver record already exists', [
                    'order_id' => $orderId,
                    'provider' => $provider,
                    'existing_id' => $existingRecord->id
                ]);
                return $existingRecord;
            }

            // إنشاء سجل جديد
            $orderDriver = new OrderDriver();
            $orderDriver->provider = $provider;
            $orderDriver->order_id = $orderId;
            $orderDriver->status = $status;
            $orderDriver->time_changed = now()->toISOString();

            // إضافة البيانات الإضافية إذا وُجدت
            if (isset($additionalData['external_ride_id'])) {
                $orderDriver->external_ride_id = $additionalData['external_ride_id'];
            }

            if (isset($additionalData['vendor_id'])) {
                $orderDriver->vendor_id = $additionalData['vendor_id'];
            }

            if (isset($additionalData['driver_id'])) {
                $orderDriver->driver_id = $additionalData['driver_id'];
            }

            if (isset($additionalData['start_lat'])) {
                $orderDriver->start_lat = $additionalData['start_lat'];
            }

            if (isset($additionalData['start_lng'])) {
                $orderDriver->start_lng = $additionalData['start_lng'];
            }

            if (isset($additionalData['end_lat'])) {
                $orderDriver->end_lat = $additionalData['end_lat'];
            }

            if (isset($additionalData['end_lng'])) {
                $orderDriver->end_lng = $additionalData['end_lng'];
            }

            if (isset($additionalData['trip_cost'])) {
                $orderDriver->trip_cost = $additionalData['trip_cost'];
            }

            $orderDriver->save();

            Log::info('OrderDriver record created successfully', [
                'order_id' => $orderId,
                'provider' => $provider,
                'status' => $status,
                'record_id' => $orderDriver->id,
                'additional_data' => $additionalData
            ]);

            return $orderDriver;

        } catch (\Exception $e) {
            Log::error('Failed to create OrderDriver record', [
                'order_id' => $orderId,
                'provider' => $provider,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    private function mapStatus(string $incoming): string
    {
        $k = strtolower(trim($incoming));
        return match ($k) {
            'accepted'                    => 'ACCEPTED',
            'arriving'                    => 'ARRIVING',
            'arrived'                     => 'ARRIVED',
            'in_progress', 'inprogress'   => 'IN_PROGRESS',
            'finished', 'completed'       => 'COMPLETED',
            'canceled', 'cancelled'       => 'CANCELLED',
            default                       => 'UNKNOWN',
        };
    }
}
