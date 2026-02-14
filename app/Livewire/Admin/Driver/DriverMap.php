<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Area;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Vendor;
use Livewire\Component;

class DriverMap extends Component
{
    public $drivers = [];
    public $role = '';
    public $vehicle_type = '';

    protected $queryString = [
        'role' => ['except' => ''],
        'vehicle_type' => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless(auth()->user()->can('drivers_map'), 403);
        $this->loadDrivers();
    }

    /** التحديث الموحد لكل الفلاتر */
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['role', 'vehicle_type'])) {
            $this->loadDrivers();
        }
    }

    public function clearFilters()
    {
        $this->role = '';
        $this->vehicle_type = '';
        $this->loadDrivers();
    }

    public function loadDrivers()
    {
        try {
            $query = Driver::query()->with(['vendor']);

            if ($this->role) {
                $query->where('role', $this->role);
            }

            if ($this->vehicle_type) {
                $query->where('vehicle_type', $this->vehicle_type);
            }
            $drivers = $query->get();

            $this->drivers = $drivers->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->phone,
                    'vendor_name' => $driver->vendor->business_name ?? 'N/A',
                    'location' => $driver->location,
                    'lat' => (float)($driver->lat ?? 0),
                    'lng' => (float)($driver->lng ?? 0),
                ];
            })->filter(function ($o) {
                return is_numeric($o['lat']) && is_numeric($o['lng'])
                    && $o['lat'] != 0 && $o['lng'] != 0
                    && $this->isWithinSaudiArabia($o['lat'], $o['lng']);
            })->values()->toArray();

            // أرسل الحدث مع البيانات لتحديث الخريطة بدون إعادة بنائها
            $this->dispatch('driversUpdated', drivers: $this->drivers);

        } catch (\Throwable $e) {
            $this->drivers = [];
            session()->flash('error', 'Error loading drivers: ' . $e->getMessage());
            // أرسل حدث بتفريغ الخريطة
            $this->dispatch('driversUpdated', drivers: []);
        }
    }

    private function formatAddress($address)
    {
        if (!$address) return 'No address';

        $parts = [];
        if (!empty($address->building)) $parts[] = $address->building;
        if (!empty($address->appartment)) $parts[] = 'Apt ' . $address->appartment;
        if (!empty($address->floor)) $parts[] = 'Floor ' . $address->floor;
        if (!empty($address->area)) $parts[] = $address->area;

        return implode(', ', $parts) ?: 'Address not specified';
    }

    private function isWithinSaudiArabia($lat, $lng)
    {
        return $lat >= 16.0 && $lat <= 32.2 && $lng >= 34.5 && $lng <= 55.7;
    }

    public function render()
    {
        return view('livewire.admin.drivers.driver-map')
            ->layout('components.layouts.admin-dashboard');
    }
}
