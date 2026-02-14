<?php

namespace App\Livewire\Admin\Order;

use App\Models\B2bClient;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Driver;
use App\Models\UserAddress;
use App\Services\DriverRequestService;
use App\Services\FazzaDeliveryService;
use App\Services\LeajlakService;
use App\Services\OrderCancellationService;
use App\Services\ReferralService;
use App\Services\StatusSmsWhatsappService;
use App\Services\WebhookRideService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class EditOrder extends Component
{

    protected StatusSmsWhatsappService $sms;
    protected WebhookRideService $webhookService;

    // Livewire v3 يدعم boot للـ DI
    public function boot(StatusSmsWhatsappService $sms, WebhookRideService $webhookService)
    {
        $this->sms = $sms;
        $this->webhookService = $webhookService;
    }

    public Order $order;

    // جميع الحقول من جدول orders
    public $order_code = '';
    public $pickup_date = '';
    public $pickup_time = '';
    public $dropoff_date = '';
    public $dropoff_time = '';
    public $instructions = '';
    public $pay_method = '';
    public $pay_status = '';
    public $sub_total = '';
    public $promo_code = '';
    public $promo_discount = '';
    public $vat = '';
    public $delivery_fee = '';
    public $grand_total = '';
    public $due_amount = '';
    public $commission_amount = '';
    public $status = '';
    public $item_status = '';
    public $user_id = '';
    public $sorting = '';
    public $address_id = '';
    public $driver_id = '';
    public $pickup_driver_id = null;
    public $delivery_driver_id = null;

    public $bill = '';
    public $payment_response = '';
    public $order_image = '';
    public $deliver_image = '';
    public $deliver_lat = '';
    public $deliver_lng = '';
    public $vendor_id = '';

    // قوائم للـ dropdown
    public $vendors = [];
    public $drivers = [];

    // بيانات المستخدم والعنوان للعرض فقط
    public $user_display = '';
    public $address_display = '';

    public function mount(Order $order)
    {
        abort_unless(auth()->user()->can('update_order'), 403);

        $this->order = $order;

        Log::info('EditOrder mount called', [
            'order_id' => $order->id,
            'order_code' => 'CB' . $order->id,
            'status' => $order->status,
            'pay_status' => $order->pay_status
        ]);

        $this->order_code = 'CB' . $order->id;
        $this->pickup_date = $order->pickup_date;
        $this->pickup_time = $order->pickup_time;
        $this->dropoff_date = $order->dropoff_date;
        $this->dropoff_time = $order->dropoff_time;
        $this->instructions = $order->instructions;
        $this->pay_method = $order->pay_method;
        $this->pay_status = $order->pay_status;
        $this->sub_total = $order->sub_total;
        $this->promo_code = $order->promo_code;
        $this->promo_discount = $order->promo_discount;
        $this->vat = $order->vat;
        $this->delivery_fee = $order->delivery_fee;
        $this->grand_total = $order->grand_total;
        $this->due_amount = $order->due_amount;
        $this->commission_amount = $order->commission_amount;
        $this->status = $order->status;
        $this->item_status = $order->item_status;
        $this->user_id = $order->user_id !== null ? (string)$order->user_id : null;
        $this->sorting = $order->sorting;
        $this->address_id = $order->address_id !== null ? (string)$order->address_id : null;
        $this->driver_id = $order->driver_id !== null ? (string)$order->driver_id : null;
        $this->pickup_driver_id = $order->pickup_driver_id;
        $this->delivery_driver_id = $order->delivery_driver_id;
        $this->bill = $order->bill;
        $this->payment_response = $order->payment_response;
        $this->order_image = $order->order_image;
        $this->deliver_image = $order->deliver_image;
        $this->deliver_lat = $order->deliver_lat;
        $this->deliver_lng = $order->deliver_lng;
        $this->vendor_id = $order->vendor_id !== null ? (string)$order->vendor_id : null;

        // تحميل بيانات المستخدم والعنوان للعرض
        $this->loadUserAndAddressDisplay();

        Log::info('EditOrder properties set', [
            'order_code' => $this->order_code,
            'status' => $this->status,
            'pay_status' => $this->pay_status,
            'user_id' => $this->user_id
        ]);

        // تحميل قوائم الـ dropdown
        $this->loadDropdownData();
    }

    public function loadUserAndAddressDisplay()
    {
        // تحميل بيانات المستخدم للعرض
        if ($this->user_id) {

            $client = B2bClient::find($this->user_id);

            if ($client) {
                $this->user_display = $client->conatact_person . ' ' . $client->company_name . ' (' . $client->phone . ')';

            } else {
                $user = User::select('id', 'first_name', 'last_name', 'phone')
                    ->find($this->user_id);
                if ($user) {
                    $this->user_display = $user->first_name . ' ' . $user->last_name . ' (' . $user->phone . ')';
                }
            }
        }

        // تحميل بيانات العنوان للعرض
        if ($this->address_id) {
            $address = UserAddress::select('id', 'area', 'building', 'appartment', 'floor')
                ->find($this->address_id);
            if ($address) {
                $this->address_display = ($address->area ?: 'N/A') . ' - ' .
                    ($address->building ?: 'N/A') . ' - ' .
                    ($address->appartment ?: 'N/A') . ' - ' .
                    ($address->floor ?: 'N/A');
            }
        }
    }

    public function loadDropdownData()
    {
        Log::info('Loading dropdown data...');

        // تحميل الشركاء
        $this->vendors = Vendor::select('id', 'business_name', 'first_name', 'last_name')
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => (string)$vendor->id,
                    'name' => $vendor->business_name . ' - ' . $vendor->first_name . ' ' . $vendor->last_name
                ];
            });

        // تحميل السائقين
        $this->drivers = Driver::select('id', 'name', 'phone')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => (string)$driver->id,
                    'name' => $driver->name . ' (' . $driver->phone . ')'
                ];
            });

        Log::info('Dropdown data loaded', [
            'vendors_count' => count($this->vendors),
            'drivers_count' => count($this->drivers)
        ]);
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'pickup_date' => 'nullable|date',
            'pickup_time' => 'nullable|string',
            'dropoff_date' => 'nullable|date',
            'dropoff_time' => 'nullable|string',
            'instructions' => 'nullable|string',
            'pay_method' => 'nullable|string',
            'pay_status' => 'required|in:Unpaid,Paid,Partial',
            'sub_total' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string',
            'promo_discount' => 'nullable|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:DRAFT,PLACED,PICKED_UP,ON_THE_WAY_FOR_PICKUP,ON_THE_WAY_TO_PARTNER,ARRIVED,PROCESSING,CONFIRMED_PAID,READY_TO_DELIVER,PICKED_FOR_DELIVER,DELIVERED,CANCELLED',
            'item_status' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'sorting' => 'nullable|in:client,vendor',
            'address_id' => 'nullable|exists:user_address,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'pickup_driver_id' => 'nullable|exists:drivers,id',
            'delivery_driver_id' => 'nullable|exists:drivers,id',
            'deliver_lat' => 'nullable|string',
            'deliver_lng' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);
    }


    public function save()
    {
        Log::info('EditOrder save method called', [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'pay_status' => $this->pay_status,
            'user_id' => $this->user_id
        ]);

        try {
            $this->validate([
                'pickup_date' => 'nullable|date',
                'pickup_time' => 'nullable|string',
                'dropoff_date' => 'nullable|date',
                'dropoff_time' => 'nullable|string',
                'instructions' => 'nullable|string',
                'pay_method' => 'nullable|string',
                'pay_status' => 'required|in:Unpaid,Paid,Partial',
                'sub_total' => 'nullable|numeric|min:0',
                'promo_code' => 'nullable|string',
                'promo_discount' => 'nullable|numeric|min:0',
                'vat' => 'nullable|numeric|min:0',
                'delivery_fee' => 'nullable|numeric|min:0',
                'grand_total' => 'nullable|numeric|min:0',
                'due_amount' => 'nullable|numeric|min:0',
                'commission_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:DRAFT,PLACED,PICKED_UP,ON_THE_WAY_FOR_PICKUP,ON_THE_WAY_TO_PARTNER,ARRIVED,PROCESSING,CONFIRMED_PAID,READY_TO_DELIVER,PICKED_FOR_DELIVER,DELIVERED,CANCELLED',
                'item_status' => 'nullable|string',
                'user_id' => 'required|exists:users,id',
                'sorting' => 'nullable|in:client,vendor',
                'address_id' => 'nullable|exists:user_address,id',
                'driver_id' => 'nullable|exists:drivers,id',
                'pickup_driver_id' => 'nullable|exists:drivers,id',
                'delivery_driver_id' => 'nullable|exists:drivers,id',
                'deliver_lat' => 'nullable|string',
                'deliver_lng' => 'nullable|string',
                'vendor_id' => 'nullable|exists:vendors,id',
            ]);

            $originalStatus = $this->order->status;

            $updateData = [
                'pickup_date' => $this->pickup_date,
                'pickup_time' => $this->pickup_time,
                'dropoff_date' => $this->dropoff_date,
                'dropoff_time' => $this->dropoff_time,
                'instructions' => $this->instructions,
                'pay_method' => $this->pay_method,
                'pay_status' => $this->pay_status,
                'sub_total' => $this->sub_total,
                'promo_code' => $this->promo_code,
                'promo_discount' => $this->promo_discount,
                'vat' => $this->vat,
                'delivery_fee' => $this->delivery_fee,
                'grand_total' => $this->grand_total,
                'due_amount' => $this->due_amount,
                'commission_amount' => $this->commission_amount,
                'status' => $this->status,
                'item_status' => $this->item_status,
                'user_id' => $this->user_id,
                'sorting' => $this->sorting,
                'address_id' => $this->address_id,
                'driver_id' => $this->driver_id,
                'pickup_driver_id' => $this->pickup_driver_id,
                'delivery_driver_id' => $this->delivery_driver_id,
                'deliver_lat' => $this->deliver_lat,
                'deliver_lng' => $this->deliver_lng,
                'vendor_id' => $this->vendor_id,
            ];

            Log::info('Updating order with data', $updateData);

            // التحقق من تغيير الحالة إلى CANCELLED وإرجاع المبالغ
            if ($originalStatus !== 'CANCELLED' && $this->status === 'CANCELLED') {
                $cancellationService = new OrderCancellationService();
                $result = $cancellationService->processOrderCancellation($this->order, 'admin');

                if ($result['success']) {
                    Log::info('Order cancellation processed successfully', [
                        'order_id' => $this->order->id,
                        'refund_amount' => $result['total_refund_amount'],
                        'package_refunded' => $result['package_refunded']
                    ]);
                } else {
                    Log::error('Order cancellation failed', [
                        'order_id' => $this->order->id,
                        'error' => $result['error']
                    ]);
                }
            }

            $this->order->update($updateData);

            // أرسل إشعار واتساب فقط إذا تغيّرت الحالة
            if ($originalStatus !== $this->status) {

                $this->order->load('user:id,phone');
                $rawPhone = $this->order->user?->phone;
                $phone = $rawPhone ? ltrim($rawPhone, '966') : null;
                $name = $this->order->user?->first_name . ' ' . $this->order->user?->last_name;
                if (!$phone) {
                    Log::warning('SMS skipped: user phone missing', ['order_id' => $this->order->id]);
                } else {
                    // خريطة الحالات → أي Endpoint نستعمل
                    switch ($this->status) {
                        case 'ON_THE_WAY_FOR_PICKUP':
                            $resp = $this->sms->pickupOrder($name, $phone);
                            Log::info('WhatsApp pickupOrder sent', ['order_id' => $this->order->id, 'phone' => $phone, 'resp' => $resp]);

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

                            break;

                        case 'ON_THE_WAY_TO_PARTNER':
                            $resp = $this->sms->deliverOrder($name, $phone);
                            Log::info('WhatsApp deliverOrder sent', ['order_id' => $this->order->id, 'phone' => $phone, 'resp' => $resp]);
                            break;

                        case 'READY_TO_DELIVER':
                            $result = (new DriverRequestService())->sendDeliveryRequestToDrivers(
                                $this->order,
                                $this->order->vendor_id,
                                $this->order->deliveryAddress->lat,
                                $this->order->deliveryAddress->lng
                            );
                            break;
                    }
                }
            }


            Log::info('Order updated successfully', ['order_id' => $this->order->id]);
            Log::info('Order updated User', ['User' => $this->order->user]);

            if ($this->status === 'ON_THE_WAY_TO_PARTNER') {
                // قائمة المغاسل التي ترسل إلى لاجلك
                $leajlakVendors = [72, 77, 78, 81, 83, 84, 86, 87, 88, 89, 92];

                // قائمة المغاسل التي ترسل إلى فزاع
                $fazzaVendors = [73, 74, 75, 79, 80, 82, 85, 90, 91];

                $orderAssigned = false;

                if (in_array($this->vendor_id, $leajlakVendors)) {
                    Log::info('Sending order to Leajlak', ['order_id' => $this->order->id, 'vendor_id' => $this->vendor_id]);
                    LeajlakService::sendOrderToLeajlak($this->order);
                    $orderAssigned = true;
                } elseif (in_array($this->vendor_id, $fazzaVendors)) {
                    Log::info('Sending order to Fazza', ['order_id' => $this->order->id, 'vendor_id' => $this->vendor_id]);
                    // إرسال الطلب إلى فزاع
                    try {
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
                            'start_latitude' => $this->order->vendor->lat ?? 0, // إحداثيات المغسلة (نقطة البداية)
                            'start_longitude' => $this->order->vendor->lng ?? 0,
                            'start_address' => $vendorAddressText,
                            'end_latitude' => $customerAddress->lat ?? 0, // إحداثيات العميل (الوجهة)
                            'end_longitude' => $customerAddress->lng ?? 0,
                            'end_address' => $customerAddressText,
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
                        Log::info('Order sent to Fazza successfully', ['id' => $this->order->id, 'payload' => $payload]);

                        // إنشاء سجل OrderDriver
                        $this->createOrderDriverRecord('fazaa', [
                            'vendor_id' => $this->order->vendor_id,
                            'start_lat' => $this->order->vendor->lat ?? 0,
                            'start_lng' => $this->order->vendor->lng ?? 0,
                            'end_lat' => $customerAddress->lat ?? 0,
                            'end_lng' => $customerAddress->lng ?? 0,
                        ]);

                        $orderAssigned = true;
                    } catch (\Exception $ex) {
                        Log::error('Failed to send order to Fazza', [
                            'id' => $this->order->id,
                            'error' => $ex->getMessage()
                        ]);
                        $orderAssigned = false;
                    }
                }

                // إذا لم يتم إسناد الطلب لأي خدمة
                if (!$orderAssigned) {
                    Log::warning('Order not assigned to any delivery service', [
                        'order_id' => $this->order->id,
                        'vendor_id' => $this->vendor_id,
                        'status' => $this->status,
                        'leajlak_vendors' => $leajlakVendors,
                        'fazza_vendors' => $fazzaVendors
                    ]);

                    $warningMessage = 'تحذير: لم يتم إسناد الطلب رقم ' . $this->order->id . ' إلى أي خدمة توصيل. رقم المغسلة: ' . $this->vendor_id . ' - يرجى التحقق من إعدادات المغسلة';
                    Log::info('Dispatching warning message', ['message' => $warningMessage]);
                    $this->dispatch('warning', $warningMessage);
                    return; // لا نكمل إذا لم يتم الإسناد
                }
            }

            // Process referral reward for first delivered order
            if ($this->status === 'DELIVERED') {
                $referralService = new ReferralService();
                $referralService->processReferralReward($this->order);
            }

            $this->dispatch('success', 'Order Updated Successfully!');

            if ($this->order->type == 'b2b') {
                return $this->redirectRoute('b2b-orders.index', navigate: true);
            } else {
                return $this->redirectRoute('admin.orders', navigate: true);
            }
        } catch (\Exception $e) {
            Log::error('Error updating order', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * create an OrderDriver record
     */
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
                Log::info('OrderDriver record created from EditOrder', [
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


    public function render()
    {
        Log::info('EditOrder render called', [
            'order_code' => $this->order_code,
            'status' => $this->status,
            'pay_status' => $this->pay_status,
            'user_id' => $this->user_id,
            'vendors_count' => count($this->vendors)
        ]);

        return view('livewire.admin.orders.edit-order')->layout('components.layouts.admin-dashboard');
    }
}
