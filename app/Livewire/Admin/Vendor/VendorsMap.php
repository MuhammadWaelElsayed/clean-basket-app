<?php

namespace App\Livewire\Admin\Vendor;

use App\Models\Vendor;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

class VendorsMap extends Component
{
    public $vendors = [];

    public function mount()
    {
        try {
            $this->vendors = Vendor::select('id', 'business_name', 'location', 'lat', 'lng', 'areas')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->whereNull('deleted_at')
                ->where('lat', '!=', '')
                ->where('lng', '!=', '')
                ->where('status', 1)
                ->get()
                ->filter(function($vendor) {
                    return is_numeric($vendor->lat) && is_numeric($vendor->lng);
                })
                ->values();
        } catch (\Exception $e) {
            $this->vendors = [];
        }
    }

    public function render()
    {
        return view('livewire.admin.vendors.vendors-map')
            ->layout('components.layouts.admin-dashboard');
    }
}
