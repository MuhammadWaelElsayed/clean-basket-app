<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Area;
use App\Models\Order;
use App\Models\Vendor;
use Livewire\Component;

class OrdersMap extends Component
{
    public $orders = [];
    public $statusFilter = '';
    public $search = '';
    public $vendorFilter = '';
    public $carpetFilter = false;
    public $areaFilter = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'search'       => ['except' => ''],
        'vendorFilter' => ['except' => ''],
        'carpetFilter' => ['except' => false],
        'areaFilter'   => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless(auth()->user()->can('orders_map'), 403);
        $this->loadOrders();
    }

    /** التحديث الموحد لكل الفلاتر */
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['statusFilter', 'search', 'vendorFilter', 'carpetFilter', 'areaFilter'])) {
            $this->loadOrders();
        }
    }

    public function clearFilters()
    {
        $this->statusFilter = '';
        $this->search = '';
        $this->vendorFilter = '';
        $this->carpetFilter = false;
        $this->areaFilter = '';
        $this->loadOrders();
    }

    public function loadOrders()
    {
        try {
            $query = Order::with(['user', 'deliveryAddress', 'vendor', 'orderItems.item'])
                ->whereIn('status', ['PLACED', 'READY_TO_DELIVER'])
                ->whereHas('deliveryAddress', function ($q) {
                    $q->whereNotNull('lat')
                      ->whereNotNull('lng')
                      ->where('lat', '!=', '')
                      ->where('lng', '!=', '');
                });

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            if ($this->vendorFilter) {
                $query->where('vendor_id', $this->vendorFilter);
            }

            if ($this->carpetFilter) {
                $carpetItemIds = [94, 100, 1008, 1052]; // المتوفر حالياً
                $query->whereHas('orderItems', function ($q) use ($carpetItemIds) {
                    $q->whereIn('item_id', $carpetItemIds);
                });
            }

            if ($this->areaFilter) {
                $query->whereHas('deliveryAddress', function ($q) {
                    $q->where('area', $this->areaFilter);
                });
            }

            if ($this->search) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $orders = $query->get();

            $this->orders = $orders->map(function ($order) {
                $carpetItemIds = [94];
                $hasCarpet = $order->orderItems->whereIn('item_id', $carpetItemIds)->count() > 0;

                return [
                    'id'             => $order->id,
                    'order_code'     => $order->order_code,
                    'status'         => $order->status,
                    'customer_name'  => $order->user ? trim(($order->user->first_name ?? '').' '.($order->user->last_name ?? '')) : 'Unknown',
                    'customer_phone' => $order->user->phone ?? '',
                    'lat'            => (float) ($order->deliveryAddress?->lat ?? 0),
                    'lng'            => (float) ($order->deliveryAddress?->lng ?? 0),
                    'address'        => $this->formatAddress($order->deliveryAddress),
                    'created_at'     => $order->created_at,
                    'grand_total'    => $order->grand_total,
                    'vendor_name'    => $order->vendor->business_name ?? null,
                    'area_name'      => ($order->deliveryAddress && $order->deliveryAddress?->area) ? $order->deliveryAddress->area : null,
                    'has_carpet'     => $hasCarpet,
                ];
            })->filter(function ($o) {
                return is_numeric($o['lat']) && is_numeric($o['lng'])
                    && $o['lat'] != 0 && $o['lng'] != 0
                    && $this->isWithinSaudiArabia($o['lat'], $o['lng']);
            })->values()->toArray();

            // أرسل الحدث مع البيانات لتحديث الخريطة بدون إعادة بنائها
            $this->dispatch('ordersUpdated', orders: $this->orders);

        } catch (\Throwable $e) {
            $this->orders = [];
            session()->flash('error', 'Error loading orders: '.$e->getMessage());
            // أرسل حدث بتفريغ الخريطة
            $this->dispatch('ordersUpdated', orders: []);
        }
    }

    private function formatAddress($address)
    {
        if (!$address) return 'No address';

        $parts = [];
        if (!empty($address->building))   $parts[] = $address->building;
        if (!empty($address->appartment)) $parts[] = 'Apt '.$address->appartment;
        if (!empty($address->floor))      $parts[] = 'Floor '.$address->floor;
        if (!empty($address->area))       $parts[] = $address->area;

        return implode(', ', $parts) ?: 'Address not specified';
    }

    private function isWithinSaudiArabia($lat, $lng)
    {
        return $lat >= 16.0 && $lat <= 32.2 && $lng >= 34.5 && $lng <= 55.7;
    }

    public function render()
    {
        return view('livewire.admin.orders.orders-map', [
            'vendors' => Vendor::where('status', 1)->orderBy('business_name')->get(['id', 'business_name']),
            'areas'   => Area::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ])->layout('components.layouts.admin-dashboard');
    }
}
