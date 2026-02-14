<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\FazzaDeliveryService;
use App\Services\WebhookRideService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderToProvider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Order $order;
    public $webhookService;
    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->webhookService = new WebhookRideService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Assigning order after pickup', ['order_id' => $this->order->id]);

            // تحميل العلاقات المطلوبة
            $this->order->load(['user', 'deliveryAddress', 'vendor']);

            $fazzaService = new FazzaDeliveryService();

            // عنوان العميل (عنوان التوصيل)
            $customerAddress = $this->order->deliveryAddress;
            $customerAddressText = $customerAddress ?
                ($customerAddress->area ?? '') . ' - ' .
                ($customerAddress->building ?? '') . ' - ' .
                ($customerAddress->appartment ?? '') . ' - ' .
                ($customerAddress->floor ?? '') : 'Customer Address';

            // عنوان المغسلة
            $vendorAddressText = 'Vendor Address';
            if ($this->order->vendor) {
                $vendor = $this->order->vendor;
                $vendorAddressText = ($vendor->area ? $vendor->area->name : '') . ' - ' .
                    ($vendor->business_name ?? '') . ' - ' .
                    ($vendor->first_name ?? '') . ' ' . ($vendor->last_name ?? '');
            }

            $payload = [
                'service_id' => 1, // Default service ID
                'id' => $this->order->id,
                'datetime' => Carbon::now()->format('Y-m-d H:i:s'),
                'start_latitude' => $customerAddress->lat ?? 0, // إحداثيات العميل
                'start_longitude' => $customerAddress->lng ?? 0,
                'start_address' => $customerAddressText,
                'end_latitude' => $this->order->vendor->lat ?? 0, // إحداثيات المغسلة
                'end_longitude' => $this->order->vendor->lng ?? 0,
                'end_address' => $vendorAddressText,
                'distance' => '',
                'total_amount' => '',
                'seat_count' => '',
                'payment_type' => 'provider',
                'payment_status' => 0,
                'user_Id' => (string)$this->order->user->id,
                'username' => $this->order->user->first_name . ' ' . $this->order->user->last_name,
                'email' => $this->order->user->email ?? '',
                'phone' => $phone ?? '',
                'status' => 'new_ride_requested',
                'trip_cost' => ''
            ];

            $fazzaService->createRideRequest($payload);
            Log::info('Order assigned successfully', ['id' => $this->order->id, 'payload' => $payload]);

            // إنشاء سجل OrderDriver
            $this->createOrderDriverRecord('fazaa', [
                'vendor_id' => $this->order->vendor_id,
                'start_lat' => $customerAddress->lat ?? 0,
                'start_lng' => $customerAddress->lng ?? 0,
                'end_lat' => $this->order->vendor->lat ?? 0,
                'end_lng' => $this->order->vendor->lng ?? 0,
            ]);
        } catch (\Exception $ex) {
            Log::error('Order assignment failed', [
                'id' => $this->order->id,
                'error' => $ex->getMessage()
            ]);
        }
    }

    private function createOrderDriverRecord(string $provider, array $additionalData = []): void
    {
        try {
            $orderDriver = $this->webhookService->createOrderDriverRecord(
                $this->order->id,
                $provider,
                'NEW',
                $additionalData
            );

            if ($orderDriver) {
                Log::info('OrderDriver record created from Engine', [
                    'order_id' => $this->order->id,
                    'provider' => $provider,
                    'record_id' => $orderDriver->id
                ]);
            } else {
                Log::warning('Failed to create OrderDriver record from EditOrder', [
                    'order_id' => $this->order->id,
                    'provider' => $provider
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating OrderDriver record from EditOrder', [
                'order_id' => $this->order->id,
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
        }
    }
}
